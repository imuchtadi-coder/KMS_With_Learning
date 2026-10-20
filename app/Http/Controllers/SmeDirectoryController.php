<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use App\Models\SkillEndorsement;
use App\Models\User;
use App\Models\UserSkill;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SmeDirectoryController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $user = $request->user();

        $query = User::query()
            ->when(!$user->hasRole('superadmin'), fn($q) => $q->where('tenant_id', $tenantId))
            ->whereHas('userSkills')
            ->with([
                'userSkills.skill',
                'userSkills.endorsements.endorser:id,name',
            ]);

        // Search by expert name or skill
        if ($request->filled('search')) {
            $search = strtolower(trim($request->search));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(department) LIKE ?', ["%{$search}%"])
                  ->orWhereHas('userSkills.skill', function ($sq) use ($search) {
                      $sq->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                  });
            });
        }

        // Filter by skill taxonomy category
        if ($request->filled('category')) {
            $category = $request->category;
            $query->whereHas('userSkills.skill', function ($sq) use ($category) {
                $sq->where('category', $category);
            });
        }

        // Filter by verified SME only
        if ($request->boolean('verified_only')) {
            $query->whereHas('userSkills', function ($sq) {
                $sq->where('is_verified', true);
            });
        }

        $experts = $query->paginate(12)->withQueryString();

        // Skill categories for filter
        $categories = Skill::query()
            ->when(!$user->hasRole('superadmin'), fn($q) => $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id'))
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();

        // Full catalog for user skill selection
        $skillCatalog = Skill::query()
            ->when(!$user->hasRole('superadmin'), fn($q) => $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id'))
            ->orderBy('name')
            ->get(['id', 'name', 'category', 'description']);

        $canVerify = $user->hasRole(['superadmin', 'tenant_admin', 'instructor']);

        return Inertia::render('Sme/Index', [
            'experts' => $experts,
            'categories' => $categories,
            'skillCatalog' => $skillCatalog,
            'filters' => $request->only(['search', 'category', 'verified_only']),
            'canVerify' => $canVerify,
            'currentUserId' => $user->id,
        ]);
    }

    public function endorse(Request $request, UserSkill $userSkill)
    {
        $endorser = $request->user();

        if ($userSkill->user_id === $endorser->id) {
            return redirect()->back()->with('error', 'Anda tidak dapat memberikan endorsement pada keahlian sendiri.');
        }

        $existing = SkillEndorsement::where('user_skill_id', $userSkill->id)
            ->where('endorsed_by', $endorser->id)
            ->first();

        if ($existing) {
            // Toggle off endorsement
            $existing->delete();
            $userSkill->decrement('endorsements_count');
            return redirect()->back()->with('success', 'Endorsement ditarik kembali.');
        }

        SkillEndorsement::create([
            'user_skill_id' => $userSkill->id,
            'endorsed_by' => $endorser->id,
            'notes' => $request->input('notes'),
        ]);

        $userSkill->increment('endorsements_count');

        return redirect()->back()->with('success', 'Berhasil memberikan endorsement keahlian kepada rekan kerja!');
    }

    public function addSkill(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'skill_id' => 'required|exists:skills,id',
            'proficiency_level' => 'required|in:beginner,intermediate,advanced,expert',
        ]);

        $userSkill = UserSkill::firstOrCreate(
            [
                'user_id' => $user->id,
                'skill_id' => $validated['skill_id'],
            ],
            [
                'proficiency_level' => $validated['proficiency_level'],
                'endorsements_count' => 0,
                'is_verified' => false,
            ]
        );

        return redirect()->back()->with('success', 'Keahlian baru berhasil ditambahkan ke profil Anda!');
    }

    public function deleteSkill(Request $request, UserSkill $userSkill)
    {
        $user = $request->user();

        if ($userSkill->user_id !== $user->id && !$user->hasRole(['superadmin', 'tenant_admin'])) {
            abort(403, 'Akses ditolak.');
        }

        $userSkill->delete();

        return redirect()->back()->with('success', 'Keahlian berhasil dihapus dari profil.');
    }

    public function verifySkill(Request $request, UserSkill $userSkill)
    {
        $user = $request->user();
        if (!$user->hasRole(['superadmin', 'tenant_admin', 'instructor'])) {
            abort(403, 'Hanya Administrator atau Lead SME yang dapat memverifikasi kepakaran.');
        }

        $userSkill->update([
            'is_verified' => !$userSkill->is_verified,
            'verified_by' => !$userSkill->is_verified ? $user->id : null,
            'verified_at' => !$userSkill->is_verified ? now() : null,
        ]);

        $statusText = $userSkill->is_verified ? 'terverifikasi sebagai SME resmi' : 'status verifikasi SME dibatalkan';

        return redirect()->back()->with('success', "Keahlian berhasil {$statusText}.");
    }
}
