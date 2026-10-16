import { computed, ref, watch } from 'vue'

const STORAGE_KEY = 'kitsuluh.language'
const COOKIE_KEY = 'kitsuluh_language'
const supportedLanguages = ['id', 'en']
let activeStorageKey = STORAGE_KEY
let activeCookieKey = COOKIE_KEY

const translations = {
  id: {
    'Dashboard': 'Dasbor', 'My Profile': 'Profil Saya', 'Discussions': 'Diskusi',
    'Knowledge Base': 'Knowledge Base', 'All Articles & SOP': 'Semua Dokumen & SOP', 'Write New Document': 'Tulis Dokumen / SOP Baru',
    'Semua Artikel & SOP': 'Semua Artikel & SOP', 'Tulis Artikel / SOP Baru': 'Tulis Artikel / SOP Baru',
    'Tenants': 'Tenant', 'Users': 'Pengguna', 'Profile': 'Profil', 'Settings': 'Pengaturan',
    'Customer Service': 'Layanan Pelanggan', 'Logout': 'Keluar', 'Search': 'Cari',
    'Search...': 'Cari...', 'Notifications': 'Notifikasi', 'Mark all as read': 'Tandai semua telah dibaca',
    'View all': 'Lihat semua', 'Welcome!': 'Selamat datang!',
    "Here's what's happening in your tenant.": 'Berikut kabar terbaru di tenant Anda.',
    "Here's what's happening with your LMS today.": 'Berikut kabar terbaru LMS Anda hari ini.',
    'Total Users': 'Total Pengguna', 'Tenant Admins': 'Admin Tenant', 'Instructors': 'Pengajar',
    'Students': 'Siswa', 'Recent Users': 'Pengguna Terbaru', 'Add User': 'Tambah Pengguna',
    'No users yet': 'Belum ada pengguna', 'Create First User': 'Buat Pengguna Pertama',
    'Name': 'Nama', 'Email': 'Email', 'Role': 'Peran', 'Joined': 'Bergabung',
    'Actions': 'Aksi', 'No role': 'Tanpa peran', 'Welcome back!': 'Selamat datang kembali!',
    'Save': 'Simpan', 'Cancel': 'Batal', 'Delete': 'Hapus', 'Edit': 'Ubah', 'Create': 'Buat',
    'Update': 'Perbarui', 'Submit': 'Kirim', 'Back': 'Kembali', 'Close': 'Tutup',
    'Loading...': 'Memuat...', 'Loading': 'Memuat', 'No data available': 'Tidak ada data',
    'No results found': 'Hasil tidak ditemukan', 'Are you sure?': 'Apakah Anda yakin?',
    'Confirm': 'Konfirmasi', 'Yes': 'Ya', 'No': 'Tidak', 'Actions': 'Aksi',
    'Status': 'Status', 'Active': 'Aktif', 'Inactive': 'Nonaktif', 'Description': 'Deskripsi', 'Description (Optional)': 'Deskripsi (Opsional)',
    'Title': 'Judul', 'Type': 'Jenis', 'Date': 'Tanggal', 'Time': 'Waktu', 'Start Date': 'Tanggal Mulai',
    'End Date': 'Tanggal Selesai', 'Filter': 'Filter', 'Apply': 'Terapkan', 'Reset': 'Atur Ulang',
    'Showing': 'Menampilkan', 'of': 'dari', 'Page': 'Halaman', 'Previous': 'Sebelumnya',
    'Next': 'Berikutnya', 'Select': 'Pilih', 'Select an option': 'Pilih opsi', 'Required': 'Wajib diisi',
    'Email address': 'Alamat email', 'Password': 'Kata sandi', 'Current password': 'Kata sandi saat ini',
    'New password': 'Kata sandi baru', 'Confirm password': 'Konfirmasi kata sandi',
    'Remember me': 'Ingat saya', 'Forgot password?': 'Lupa kata sandi?', 'Log in': 'Masuk',
    'Login': 'Masuk', 'Register': 'Daftar', 'Account': 'Akun', 'Notifications': 'Notifikasi',
    'No notifications': 'Tidak ada notifikasi', 'View Details': 'Lihat Detail', 'View all notifications': 'Lihat semua notifikasi',
    'Courses': 'Kursus', 'Course': 'Kursus', 'Lessons': 'Pelajaran', 'Lesson': 'Pelajaran',
    'Assignments': 'Tugas', 'Assignment': 'Tugas', 'Quizzes': 'Kuis', 'Quiz': 'Kuis', 'Agency Name': 'Nama Lembaga', 'Organization Name': 'Nama Organisasi', 'Organization Tagline': 'Slogan Organisasi',
    'Grades': 'Nilai', 'Grade': 'Nilai', 'Learning Paths': 'Jalur Pembelajaran', 'My Learning Paths': 'Jalur Pembelajaran Saya',
    'Live Classes': 'Kelas Langsung', 'Leaderboard': 'Papan Peringkat', 'Gamification': 'Gamifikasi',
    'Analytics': 'Analitik', 'Reports': 'Laporan', 'Categories': 'Kategori', 'Tags': 'Tag', 'Tagline': 'Slogan', 'Settings': 'Pengaturan', 'Profile Settings': 'Pengaturan Profil', 'Account Settings': 'Pengaturan Akun', 'System Settings': 'Pengaturan Sistem',
    'Badges': 'Lencana', 'Certificates': 'Sertifikat', 'Tenants': 'Tenant', 'Branding': 'Identitas Merek',
    'Audit Logs': 'Log Audit', 'Mail Settings': 'Pengaturan Email', 'Profile Settings': 'Pengaturan Profil',
    'My Courses': 'Kursus Saya', 'Enrolled Courses': 'Kursus yang Diikuti', 'Progress': 'Progres',
    'Overview': 'Ringkasan', 'Details': 'Detail', 'Content': 'Konten', 'Resources': 'Sumber Belajar',
    'Discussion': 'Diskusi', 'Reply': 'Balas', 'Post': 'Posting', 'Send': 'Kirim', 'Publish': 'Terbitkan',
    'Draft': 'Draf', 'Published': 'Diterbitkan', 'Pending': 'Menunggu', 'Completed': 'Selesai',
    'Save Changes': 'Simpan Perubahan', 'Create New': 'Buat Baru', 'Add New': 'Tambah Baru', 'Create Lesson': 'Buat Pelajaran', 'Create Course': 'Buat Kursus', 'Create Assignment': 'Buat Tugas',
    'Are you sure you want to delete this item?': 'Yakin ingin menghapus item ini?', 'Enter organization tagline': 'Masukkan slogan organisasi', 'Enter organization name': 'Masukkan nama organisasi', 'Enter your name': 'Masukkan nama Anda', 'Enter your email': 'Masukkan email Anda', 'Enter your password': 'Masukkan kata sandi Anda',
    'This field is required': 'Kolom ini wajib diisi', 'Please enter a valid email address': 'Masukkan alamat email yang valid',
    'The email field is required': 'Kolom email wajib diisi', 'The password field is required': 'Kolom kata sandi wajib diisi',
    'Something went wrong': 'Terjadi kesalahan', 'An error occurred': 'Terjadi kesalahan', 'Enter agency or organization name': 'Masukkan nama lembaga atau organisasi', 'Enter your email address': 'Masukkan alamat email Anda',
    'Successfully saved': 'Berhasil disimpan', 'Saved successfully': 'Berhasil disimpan',
    'Created successfully': 'Berhasil dibuat', 'Updated successfully': 'Berhasil diperbarui',
    'Deleted successfully': 'Berhasil dihapus', 'Changes saved successfully': 'Perubahan berhasil disimpan',
    'Back to Dashboard': 'Kembali ke Dasbor', 'Welcome to KITsuluh': 'Selamat datang di KITsuluh',
    'English': 'English', 'Bahasa Indonesia': 'Bahasa Indonesia', 'Language': 'Bahasa',
  },
}

// Page descriptions and workflow copy used across the admin, instructor, and student areas.
Object.assign(translations.id, {
  'Learning Management System': 'Sistem Manajemen Pembelajaran',
  'Edit Profile': 'Ubah Profil', 'Update your account information': 'Perbarui informasi akun Anda',
  'Back to Profile': 'Kembali ke Profil', 'There were some errors:': 'Ada beberapa kesalahan:',
  'Profile Information': 'Informasi Profil',
  'Update your personal information and contact details.': 'Perbarui informasi pribadi dan detail kontak Anda.',
  'Update Profile': 'Perbarui Profil', 'Change Password': 'Ubah Kata Sandi',
  'Update your password to keep your account secure.': 'Perbarui kata sandi untuk menjaga keamanan akun Anda.',
  'Update Password': 'Perbarui Kata Sandi', 'Manage your account information and preferences': 'Kelola informasi dan preferensi akun Anda',
  'Phone Number': 'Nomor Telepon', 'Email Address': 'Alamat Email', 'Department': 'Departemen',
  'Date of Birth': 'Tanggal Lahir', 'Full Name': 'Nama Lengkap', 'Member Since': 'Menjadi Anggota Sejak',
  'Notifications': 'Notifikasi', 'Mark All as Read': 'Tandai Semua Telah Dibaca',
  'Mark read': 'Tandai telah dibaca', 'View': 'Lihat', "You're all caught up!": 'Tidak ada notifikasi baru!',
  'Assessment Analytics': 'Analitik Penilaian',
  'Overview of assessment performance across all courses': 'Ringkasan kinerja penilaian di semua kursus',
  'Total Courses': 'Total Kursus', 'Enrollments': 'Peserta', 'Assessments': 'Penilaian',
  'Avg Score': 'Nilai Rata-rata', 'Pass Rate': 'Tingkat Kelulusan', 'No course data available': 'Data kursus belum tersedia',
  'Instructor': 'Pengajar', 'Sections': 'Bagian', 'Created': 'Dibuat', 'Slug': 'Slug',
  'No data.': 'Tidak ada data.', 'No data yet': 'Belum ada data', 'No data yet.': 'Belum ada data.',
  'No courses found': 'Kursus tidak ditemukan', 'No courses found matching your criteria.': 'Tidak ada kursus yang sesuai dengan kriteria Anda.',
  'No badges yet': 'Belum ada lencana', 'No questions yet': 'Belum ada pertanyaan',
  'No questions available.': 'Tidak ada pertanyaan yang tersedia.', 'No questions assigned.': 'Belum ada pertanyaan yang ditambahkan.',
  'No quizzes yet': 'Belum ada kuis', 'No quizzes found': 'Kuis tidak ditemukan',
  'There are no quizzes matching your search.': 'Tidak ada kuis yang cocok dengan pencarian Anda.',
  'No live classes': 'Belum ada kelas langsung', 'No students enrolled.': 'Belum ada siswa yang terdaftar.',
  'No student attempts have been submitted for this quiz yet.': 'Belum ada siswa yang mengirimkan pengerjaan kuis ini.',
  'No essay answers to review.': 'Tidak ada jawaban esai untuk ditinjau.',
  'No essay questions. Grading is automatic.': 'Tidak ada soal esai. Penilaian dilakukan otomatis.',
  'Select a quiz to view student attempts and grading.': 'Pilih kuis untuk melihat pengerjaan dan nilai siswa.',
  'Manage quiz attempts and grade essay answers': 'Kelola pengerjaan kuis dan nilai jawaban esai',
  'Review student quiz attempts and manually grade essay answers.': 'Tinjau pengerjaan kuis siswa dan nilai jawaban esai secara manual.',
  'Create questions for quizzes and exams': 'Buat pertanyaan untuk kuis dan ujian',
  'Create questions first': 'Buat pertanyaan terlebih dahulu', 'Create questions first.': 'Buat pertanyaan terlebih dahulu.',
  'Create a quiz to assess student understanding': 'Buat kuis untuk mengukur pemahaman siswa',
  'Create Your First Quiz': 'Buat Kuis Pertama Anda', 'Create Quiz': 'Buat Kuis', 'Create New Quiz': 'Buat Kuis Baru',
  'New Quiz': 'Kuis Baru', 'Edit Quiz': 'Ubah Kuis', 'Update Quiz': 'Perbarui Kuis', 'Quiz Info': 'Info Kuis', 'Quiz Settings': 'Pengaturan Kuis', 'Quiz Questions': 'Pertanyaan Kuis', 
  'Quiz Attempts': 'Pengerjaan Kuis', 'Quiz Analytics': 'Analitik Kuis', 'Quiz Title': 'Judul Kuis', 'Quiz Description': 'Deskripsi Kuis', 'Quiz Instructions': 'Petunjuk Kuis', 'Quiz Duration': 'Durasi Kuis', 'Quiz Passing Grade': 'Nilai Kelulusan Kuis', 'Quiz Max Attempts': 'Batas Pengerjaan Kuis', 'Quiz Time Limit': 'Batas Waktu Kuis', 'Quiz Status': 'Status Kuis', 'Quiz Visibility': 'Visibilitas Kuis',
  'Create Assignment': 'Buat Tugas', 'Submit Assignment': 'Kirim Tugas', 'Back to Course': 'Kembali ke Kursus', 'Passing Grade (%)': 'Nilai Kelulusan (%)', 'Max Attempts': 'Batas Pengerjaan', 'Time Limit': 'Batas Waktu', 'Time Remaining': 'Sisa Waktu', 'Time Limit (minutes)': 'Batas Waktu (menit)', 'Time Limit:': 'Batas Waktu:', 'Time Remaining:': 'Sisa Waktu:', 'Your Score:': 'Nilai Anda:', 
  'Best Score:': 'Nilai Terbaik:', 'Grade Breakdown': 'Rincian Nilai', 'Class Avg': 'Rata-rata Kelas', 'Final Grade': 'Nilai Akhir', 'Score': 'Nilai', 'Percentage': 'Persentase', 'Shufle Questions': 'Acak Pertanyaan', 'Shufle Answers': 'Acak Jawaban', 'Allow Multiple Attempts': 'Izinkan Beberapa Pengerjaan', 'Allow Multiple Attempts?': 'Izinkan Beberapa Pengerjaan?', 
  'Allow Multiple Attempts? (If enabled, students can take the quiz multiple times.)': 'Izinkan Beberapa Pengerjaan? (Jika diaktifkan, siswa dapat mengerjakan kuis beberapa kali.)', 'Allow Multiple Attempts? (If enabled, students can take the quiz multiple times.)': 'Izinkan Beberapa Pengerjaan? (Jika diaktifkan, siswa dapat mengerjakan kuis beberapa kali.)', 
  'Allow Multiple Attempts? (If enabled, students can take the quiz multiple times.)': 'Izinkan Beberapa Pengerjaan? (Jika diaktifkan, siswa dapat mengerjakan kuis beberapa kali.)', 'Entry Grace Period (minutes)': 'Periode Toleransi Masuk (menit)', 'Entry Grace Period (minutes):': 'Periode Toleransi Masuk (menit):', 'Entry Grace Period (minutes): (If enabled, students can enter the quiz after the time limit has expired.)': 'Periode Toleransi Masuk (menit): (Jika diaktifkan, siswa dapat masuk ke kuis setelah batas waktu berakhir.)',
  'Additional time allowed to start the exam after the start time': 'Waktu tambahan yang diizinkan untuk memulai ujian setelah waktu mulai',
  'Back to My Learning': 'Kembali ke Pembelajaran Saya', 'My Learning': 'Pembelajaran Saya',
  'Course Content': 'Materi Kursus', 'Course Stats': 'Statistik Kursus', 'Course Enrollments': 'Peserta Kursus',
  'Course Performance': 'Kinerja Kursus', 'Course Info': 'Info Kursus', 'Courses in This Path': 'Kursus dalam Jalur Ini',
  'Course Progress': 'Progres Kursus', 'Overall Progress': 'Progres Keseluruhan', 'Total Lessons:': 'Total Pelajaran:',
  'Completed:': 'Selesai:', 'Total Courses': 'Total Kursus', 'Total Enrollments': 'Total Peserta',
  'Courses Completed': 'Kursus Selesai', 'Your Progress': 'Progres Anda', 'Course Stats': 'Statistik Kursus', 'Your Certificates': 'Sertifikat Anda', 'Your Badges': 'Lencana Anda', 'Your Points': 'Poin Anda', 'Your Streak': 'Rangkaian Belajar Anda',
  'Your Submission': 'Kiriman Anda', 'Submitted:': 'Dikirim:', 'Your answer:': 'Jawaban Anda:', 'Category': 'Kategori', 'Category:': 'Kategori:', 'Tags:': 'Tag:', 'Tags': 'Tag', 'Instructor:': 'Pengajar:', 'Instructor': 'Pengajar',
  'Correct answer:': 'Jawaban yang benar:', 'Instructor Feedback:': 'Masukan Pengajar:',
  'Instructions:': 'Petunjuk:', 'Upload File': 'Unggah Berkas', 'Notes (optional)': 'Catatan (opsional)',
  'Lesson Materials': 'Materi Pelajaran', 'Assessment': 'Penilaian', 'Assessments': 'Penilaian',
  'Question Bank': 'Bank Soal', 'Answer Options': 'Pilihan Jawaban', 'Select Questions': 'Pilih Pertanyaan',
  'Select a correct answer': 'Pilih jawaban yang benar', 'Select the correct answer': 'Pilih jawaban yang benar',
  'Options': 'Pilihan', '+ Add Option': '+ Tambah Pilihan', 'Add Question': 'Tambah Pertanyaan',
  'Create Question': 'Buat Pertanyaan', 'Edit Question': 'Ubah Pertanyaan', 'Update Question': 'Perbarui Pertanyaan',
  'Answer Review': 'Tinjauan Jawaban', 'Essay Questions': 'Soal Esai', 'Essay Answers': 'Jawaban Esai',
  'Essay Review': 'Tinjauan Esai', 'Manual Review': 'Tinjauan Manual', 'Auto Graded': 'Dinilai Otomatis',
  'Needs Grading': 'Perlu Dinilai', 'Graded': 'Sudah Dinilai', 'Submit Grades': 'Kirim Nilai',
  'Back to Quiz Grading': 'Kembali ke Penilaian Kuis', 'Back to Detail': 'Kembali ke Detail',
  'Back to Grades Overview': 'Kembali ke Ringkasan Nilai', 'Back to Live Classes': 'Kembali ke Kelas Langsung',
  'Back to Learning Paths': 'Kembali ke Jalur Pembelajaran', 'View Attempts': 'Lihat Pengerjaan',
  'Student Attempts': 'Pengerjaan Siswa', 'Attempts': 'Pengerjaan', 'Attempt': 'Pengerjaan',
  'Passing Grade': 'Nilai Kelulusan', 'Passing Grade:': 'Nilai Kelulusan:', 'Max Attempts:': 'Batas Pengerjaan:',
  'Time Limit': 'Batas Waktu', 'Time Limit:': 'Batas Waktu:', 'Time Remaining': 'Sisa Waktu',
  'Your Score': 'Nilai Anda', 'Best Score:': 'Nilai Terbaik:', 'Grade Breakdown': 'Rincian Nilai',
  'Class Avg': 'Rata-rata Kelas', 'Final Grade': 'Nilai Akhir', 'Score': 'Nilai', 'Percentage': 'Persentase',
  'No grades yet': 'Belum ada nilai', 'No grades available': 'Nilai belum tersedia',
  'No grades available for this course.': 'Nilai untuk kursus ini belum tersedia.',
  'Enroll in courses to start tracking your grades.': 'Ikuti kursus untuk mulai melihat nilai Anda.',
  'Download Transcript (PDF)': 'Unduh Transkrip (PDF)', 'Courses': 'Kursus', 'Enrollments': 'Peserta',
  'Enroll Now': 'Daftar Sekarang', 'Enroll to Get Started': 'Daftar untuk Memulai',
  'Enroll in this learning path to start learning!': 'Daftar ke jalur pembelajaran ini untuk mulai belajar!',
  'Start Course': 'Mulai Kursus', 'Continue Learning': 'Lanjutkan Belajar', 'Continue learning this lesson using the video above.': 'Lanjutkan pelajaran ini melalui video di atas.',
  'Explore Courses': 'Jelajahi Kursus', 'Browse Courses': 'Telusuri Kursus', 'Explore Learning Paths': 'Jelajahi Jalur Pembelajaran',
  'No Learning Paths Available': 'Belum Ada Jalur Pembelajaran', 'Check back soon for new learning paths!': 'Kunjungi kembali nanti untuk melihat jalur pembelajaran baru!',
  'Start a learning path to track your progress': 'Mulai jalur pembelajaran untuk memantau progres Anda',
  'Track your progress and continue learning': 'Pantau progres dan lanjutkan pembelajaran Anda',
  'All Paths': 'Semua Jalur', 'Next Course': 'Kursus Berikutnya', 'Courses Completed': 'Kursus Selesai',
  'What You\'ll Learn': 'Yang Akan Anda Pelajari', 'Key concepts and fundamental knowledge': 'Konsep utama dan pengetahuan dasar',
  'Practical application of the concepts': 'Penerapan konsep secara praktis', 'Best practices and industry standards': 'Praktik terbaik dan standar industri',
  'Hands-on exercises and examples': 'Latihan praktik dan contoh', 'Structured curriculum to master new skills': 'Kurikulum terstruktur untuk menguasai keterampilan baru',
  'Learning Outcomes': 'Hasil Pembelajaran', 'Preview Mode': 'Mode Pratinjau', 'Preview': 'Pratinjau',
  'No Video Available': 'Video Tidak Tersedia', 'This lesson does not have a ready video yet.': 'Video untuk pelajaran ini belum tersedia.',
  'No assessments available yet.': 'Belum ada penilaian yang tersedia.', 'No additional resources available for this lesson.': 'Belum ada sumber belajar tambahan untuk pelajaran ini.',
  'Previous Lesson': 'Pelajaran Sebelumnya', 'Next Lesson': 'Pelajaran Berikutnya', 'Previous Lesson Video': 'Video Pelajaran Sebelumnya',
  'Next Lesson Video': 'Video Pelajaran Berikutnya', 'You have completed this lesson': 'Anda telah menyelesaikan pelajaran ini',
  'Mark as Complete': 'Tandai Selesai', 'In Progress': 'Sedang Berlangsung', 'Locked': 'Terkunci',
  'This session has ended.': 'Sesi ini telah berakhir.', 'This session was cancelled.': 'Sesi ini dibatalkan.',
  'This session has ended. No recording available.': 'Sesi ini telah berakhir. Rekaman tidak tersedia.',
  'View Recording': 'Lihat Rekaman', 'Join Meeting': 'Gabung Rapat', 'Meeting ID:': 'ID Rapat:',
  'Create Live Class': 'Buat Kelas Langsung', 'Edit Live Class': 'Ubah Kelas Langsung',
  'Create Session': 'Buat Sesi', 'Update Session': 'Perbarui Sesi', 'New Discussion': 'Diskusi Baru',
  'Create Discussion': 'Buat Diskusi', 'Pinned': 'Disematkan', 'Import Users': 'Impor Pengguna',
  'Create User': 'Buat Pengguna', 'Create Tenant': 'Buat Tenant', 'Create Course': 'Buat Kursus',
  'Create New Course': 'Buat Kursus Baru', 'Create Badge': 'Buat Lencana', 'Create Path': 'Buat Jalur',
  'Create Learning Path': 'Buat Jalur Pembelajaran', 'Define a new achievement badge': 'Buat lencana pencapaian baru',
  'Gamification badges and achievements': 'Lencana dan pencapaian gamifikasi',
  'Learning Path Analytics': 'Analitik Jalur Pembelajaran', 'Overview of all learning paths': 'Ringkasan semua jalur pembelajaran',
  'Path Analytics Details': 'Detail Analitik Jalur', 'Define a structured learning journey': 'Buat alur pembelajaran yang terstruktur',
  'Manage structured learning journeys': 'Kelola alur pembelajaran yang terstruktur',
  'Organize courses by category': 'Kelompokkan kursus berdasarkan kategori', 'Add a new course category': 'Tambahkan kategori kursus baru',
  'Review and manage courses in your tenant': 'Tinjau dan kelola kursus di tenant Anda',
  'Bulk Enroll': 'Pendaftaran Massal', 'Enroll multiple students in a course at once': 'Daftarkan beberapa siswa ke kursus sekaligus',
  'Select Students': 'Pilih Siswa', 'Enroll Selected': 'Daftarkan yang Dipilih',
  'Bulk Certificates': 'Sertifikat Massal', 'Generate certificates for multiple students at once': 'Buat sertifikat untuk beberapa siswa sekaligus',
  'Select All': 'Pilih Semua', 'Deselect All': 'Batalkan Pilihan', 'Generate Certificates': 'Buat Sertifikat',
  'CSV must have headers:': 'Berkas CSV harus memiliki header:', 'JPG, JPEG, PNG, or WEBP. Maximum 2 MB.': 'JPG, JPEG, PNG, atau WEBP. Maksimal 2 MB.',
  'Delete this question?': 'Hapus pertanyaan ini?', 'Delete this live class?': 'Hapus kelas langsung ini?',
  'You have used all attempts.': 'Anda telah menggunakan semua kesempatan pengerjaan.',
  'Time\'s Up!': 'Waktu Habis!', 'Congratulations! You Passed!': 'Selamat! Anda Lulus!',
  'Unfortunately, You Did Not Pass': 'Sayang sekali, Anda Belum Lulus', 'This quiz contains essay questions that require': 'Kuis ini memiliki soal esai yang memerlukan',
  'manual grading.': 'penilaian manual.', 'No results found': 'Hasil tidak ditemukan',
  'Try different keywords or broaden your search.': 'Coba kata kunci lain atau perluas pencarian Anda.',
  'Search courses, lessons, and users': 'Cari kursus, pelajaran, dan pengguna',
  'No badges earned yet. Complete activities to earn badges!': 'Belum ada lencana. Selesaikan aktivitas untuk mendapatkannya!',
  'No points earned yet. Start learning to earn XP!': 'Belum ada poin. Mulai belajar untuk mendapatkan XP!',
  'Recent Points Activity': 'Aktivitas Perolehan Poin Terbaru', 'Current Streak': 'Rangkaian Belajar Saat Ini',
  'Class Rank': 'Peringkat Kelas', 'Total XP': 'Total XP', 'Streak': 'Rangkaian', 'Item': 'Item',
  'Quiz Result': 'Hasil Kuis', 'Found': 'Ditemukan', 'Action': 'Tindakan', 'Manage': 'Kelola',
  'Skill Level': 'Tingkat Keahlian', 'Duration': 'Durasi', 'Language': 'Bahasa', 'Price': 'Harga',
  'Level': 'Tingkat', 'Parent': 'Induk', 'Criteria': 'Kriteria', 'Value': 'Nilai', 'Instructor': 'Pengajar',
  'Unpublish': 'Jadikan Draf', 'Unpublished': 'Tidak Diterbitkan', 'Download': 'Unduh', 'Import': 'Impor',
  'Export CSV': 'Ekspor CSV', 'Submit Assignment': 'Kirim Tugas', 'Submit Quiz': 'Kirim Kuis',
  'The email field is required.': 'Kolom email wajib diisi.', 'The password field is required.': 'Kolom kata sandi wajib diisi.',
  'Add a new user to the system': 'Tambahkan pengguna baru ke sistem',
  'Bulk import users from a CSV file': 'Impor banyak pengguna dari berkas CSV',
  'Bulk import users from a CSV file.': 'Impor banyak pengguna dari berkas CSV.',
  'Configure SMTP email settings for your tenant': 'Atur konfigurasi email SMTP untuk tenant Anda',
  'Configure XP points, level thresholds, and streak milestones': 'Atur poin XP, ambang level, dan pencapaian rangkaian belajar',
  'Consecutive day streaks that trigger a bonus XP reward.': 'Rangkaian hari berturut-turut yang memberikan bonus XP.',
  'Create and manage your courses': 'Buat dan kelola kursus Anda',
  'Create your first course to get started teaching students': 'Buat kursus pertama Anda untuk mulai mengajar siswa',
  'Create Your First Course': 'Buat Kursus Pertama Anda',
  'Discover and enroll in courses in': 'Temukan dan ikuti kursus di',
  'Define XP required for each level. Leave empty to use formula-based calculation.': 'Tentukan XP yang diperlukan untuk setiap level. Kosongkan untuk menggunakan perhitungan berdasarkan rumus.',
  'Filter student berdasarkan status certificate': 'Filter siswa berdasarkan status sertifikat',
  'Manage all tenants (superadmin only)': 'Kelola semua tenant (khusus superadmin)',
  'Manage all users in your tenant': 'Kelola semua pengguna di tenant Anda',
  'Manage and create courses': 'Kelola dan buat kursus',
  'Manage branding configuration for': 'Kelola pengaturan identitas merek untuk',
  'Manage your enrolled courses and track your learning progress': 'Kelola kursus yang Anda ikuti dan pantau progres belajar',
  'New courses are created as': 'Kursus baru dibuat dengan status',
  'No Active Learning Paths': 'Tidak Ada Jalur Pembelajaran Aktif',
  'No live classes available.': 'Belum ada kelas langsung.',
  'No materials uploaded yet.': 'Belum ada materi yang diunggah.',
  'No meeting URL provided. Contact your instructor for meeting details.': 'URL rapat belum tersedia. Hubungi pengajar untuk mendapatkan detail rapat.',
  'No grade items found for this course.': 'Tidak ada komponen nilai untuk kursus ini.',
  'No certificate logo uploaded': 'Logo sertifikat belum diunggah',
  'No replies yet. Be the first to reply!': 'Belum ada balasan. Jadilah yang pertama membalas!',
  'No reviews yet. Be the first to review!': 'Belum ada ulasan. Jadilah yang pertama memberi ulasan!',
  'No students enrolled yet': 'Belum ada siswa yang terdaftar',
  'Please enter at least 2 characters to search.': 'Masukkan minimal 2 karakter untuk mencari.',
  'Ready for Processing Certificate': 'Sertifikat Siap Diproses',
  'Register a new tenant organization': 'Daftarkan organisasi tenant baru',
  'Select a course to add to this path': 'Pilih kursus untuk ditambahkan ke jalur ini',
  'Start a new discussion to engage with your peers.': 'Mulai diskusi baru bersama rekan-rekan Anda.',
  'Students will appear here once they enroll in your course.': 'Siswa akan muncul di sini setelah mendaftar ke kursus Anda.',
  'Students would see the enroll button here.': 'Siswa akan melihat tombol pendaftaran di sini.',
  'The deadline for this assignment has passed.': 'Batas waktu tugas ini telah lewat.',
  'Try adjusting your filters or create a new course': 'Coba ubah filter atau buat kursus baru',
  'Video player coming soon': 'Pemutar video akan segera tersedia',
  'This is how students will see your course.': 'Seperti inilah tampilan kursus bagi siswa.',
  'This course is currently in': 'Kursus ini saat ini berstatus',
  'state. Your tenant admin must review and approve the course before it becomes visible to students.': 'Status. Admin tenant harus meninjau dan menyetujui kursus sebelum dapat dilihat siswa.',
  'Allowed types:': 'Jenis berkas yang diizinkan:',
  'No video uploaded for this lesson': 'Belum ada video yang diunggah untuk pelajaran ini',
  'Video is processing (': 'Video sedang diproses (',
  'No additional resources available for this lesson.': 'Belum ada sumber belajar tambahan untuk pelajaran ini.',
  'LMS Kit — Inertia + Vuetify': 'LMS Kit — Inertia + Vuetify',
  'Vuexy Academy template integrated with Inertia.': 'Template Vuexy Academy terintegrasi dengan Inertia.',
  'Belajar, berkembang, dan raih kompetensi bersama KITsuluh': 'Belajar, berkembang, dan raih kompetensi bersama KITsuluh',
  'Materi tersusun sistematis': 'Materi tersusun sistematis', 'Pantau setiap pencapaian': 'Pantau setiap pencapaian',
  'Butuh bantuan? Hubungi administrator Anda.': 'Butuh bantuan? Hubungi administrator Anda.',
  'Students': 'Siswa', 'Student': 'Siswa', 'student': 'siswa', 'Tenants': 'Tenant', 'Tenant': 'Tenant',
  'Enrollments': 'Pendaftaran', 'Manage': 'Kelola', 'All Paths': 'Semua Jalur', 'My Learning': 'Pembelajaran Saya',
  'Action': 'Tindakan', 'Detail': 'Detail', 'Created': 'Dibuat', 'Joined': 'Bergabung', 'Slug': 'Slug',
  'Essay': 'Esai', 'Exam': 'Ujian', 'Exam Settings': 'Pengaturan Ujian', 'Exam Monitoring': 'Pemantauan Ujian',
  'Publish': 'Terbitkan', 'Unpublish': 'Batalkan Terbit', 'Published': 'Diterbitkan', 'Draft': 'Draf',
  'Started': 'Dimulai', 'Submitted': 'Dikirim', 'Deadline': 'Batas Waktu', 'Start:': 'Mulai:', 'End:': 'Selesai:',
  'No limit': 'Tanpa batas', 'True': 'Benar', 'False': 'Salah', 'Free': 'Gratis', 'Yes': 'Ya', 'No': 'Tidak',
  'Language': 'Bahasa', 'Model': 'Model', 'User': 'Pengguna', 'Address': 'Alamat', 'Period': 'Periode', 'Choose Logo':'Pilih Logo', 'Choose Certificate Logo':'Pilih Logo Sertifikat', 'Choose Background Image':'Pilih Gambar Latar Belakang', 'Choose Certificate Background Image':'Pilih Gambar Latar Belakang Sertifikat',
  'Provider': 'Penyedia', 'Start': 'Mulai', 'Recording': 'Rekaman', 'Certificate': 'Sertifikat',
  'Enrolled': 'Terdaftar', 'All': 'Semua', 'Incomplete': 'Belum Selesai', 'Issued': 'Diterbitkan',
  'Beginner': 'Pemula', 'Intermediate': 'Menengah', 'Advanced': 'Lanjutan', 'Select level': 'Pilih tingkat',
  'All Levels': 'Semua Tingkat', 'All Status': 'Semua Status', 'All Time': 'Sepanjang Waktu',
  'This Week': 'Minggu Ini', 'This Month': 'Bulan Ini', 'None': 'Tidak Ada',
  'Lesson Complete': 'Pelajaran Selesai', 'Course Complete': 'Kursus Selesai',
  'Quiz Perfect Score': 'Nilai Kuis Sempurna', 'Points Milestone': 'Pencapaian Poin', 'Consecutive Days': 'Hari Berturut-turut',
  'Multiple Choice': 'Pilihan Ganda', 'True/False': 'Benar/Salah', 'Question': 'Pertanyaan',
  'Section': 'Bagian', 'Max Score': 'Nilai Maksimal', 'Submissions': 'Kiriman',
  'Forbidden': 'Akses Ditolak', 'You do not have permission to access this page.': 'Anda tidak memiliki izin untuk mengakses halaman ini.',
  'Page Not Found': 'Halaman Tidak Ditemukan', 'The page you are looking for does not exist.': 'Halaman yang Anda cari tidak tersedia.',
  'Session Expired': 'Sesi Berakhir', 'Your session has expired. Please log in again.': 'Sesi Anda telah berakhir. Silakan masuk kembali.',
  'Too Many Requests': 'Terlalu Banyak Permintaan', 'You have made too many requests. Please try again later.': 'Anda mengirim terlalu banyak permintaan. Silakan coba lagi nanti.',
  'Server Error': 'Kesalahan Server', 'An unexpected error occurred. Please try again later.': 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi nanti.',
  'This action is not allowed during this exam.': 'Tindakan ini tidak diizinkan selama ujian.',
  'Are you sure you want to submit?': 'Apakah Anda yakin ingin mengirim jawaban?',
  'Delete?': 'Hapus?', 'Delete this question?': 'Hapus pertanyaan ini?',
  'Delete this assignment?': 'Hapus tugas ini?', 'Delete this path?': 'Hapus jalur ini?',
  'Remove your review?': 'Hapus ulasan Anda?',
  'Reset streak milestones to default?': 'Atur ulang pencapaian rangkaian belajar ke nilai awal?',
  'Section is required': 'Bagian wajib dipilih',
  'Remove Logo':'Hapus Logo', 'Remove Certificate Logo':'Hapus Logo Sertifikat', 'Remove Background Image':'Hapus Gambar Latar Belakang', 'Remove Certificate Background Image':'Hapus Gambar Latar Belakang Sertifikat',
  '. Your progress this week is awesome. Let\'s keep it up!': '. Progres Anda minggu ini sangat baik. Pertahankan!',
  'Add Lesson': 'Tambah Pelajaran', 'About this course': 'Tentang kursus ini', 'Active:': 'Aktif:',
  'Add a new course tag': 'Tambahkan tag kursus baru', 'Add Category': 'Tambah Kategori', 'Add Course': 'Tambah Kursus',
  'Add Level': 'Tambah Level', 'Add Material': 'Tambah Materi', 'Add Milestone': 'Tambah Pencapaian',
  'Add Section': 'Tambah Bagian', 'Add Tag': 'Tambah Tag', 'Allowed Files:': 'Berkas yang Diizinkan:',
  'and unpublished. You\'ll be able to add sections and lessons after creation.': 'dan belum diterbitkan. Anda dapat menambahkan bagian dan pelajaran setelah kursus dibuat.',
  '✓ Completed': '✓ Selesai', 'Assessment Performance': 'Kinerja Penilaian', 'Assignment Grading': 'Penilaian Tugas',
  'Assignment Info': 'Info Tugas', 'Avg Completion': 'Rata-rata Penyelesaian', 'Avg Progress': 'Rata-rata Progres',
  'Back to Discussions': 'Kembali ke Diskusi', 'Back to Enrollment': 'Kembali ke Pendaftaran',
  'Back to Overview': 'Kembali ke Ringkasan', 'Audit Log Details': 'Detail Log Audit', 'By the numbers': 'Ringkasan Angka',
  'Certificate Logo': 'Logo Sertifikat', 'Certificates Earned': 'Sertifikat yang Diperoleh', 'Clear': 'Bersihkan',
  'Complete a course to earn your first certificate': 'Selesaikan kursus untuk mendapatkan sertifikat pertama Anda',
  'Completion': 'Penyelesaian', 'Completion Rate': 'Tingkat Penyelesaian', 'Continue learning in': 'Lanjutkan belajar di',
  'Course Analytics': 'Analitik Kursus', 'Course Approval': 'Persetujuan Kursus', 'Course Details': 'Detail Kursus',
  'Course Overview': 'Ringkasan Kursus', 'Course Status': 'Status Kursus', 'Create a new course': 'Buat kursus baru',
  'Create Category': 'Buat Kategori', 'Create First Course': 'Buat Kursus Pertama', 'Create First Section': 'Buat Bagian Pertama',
  'Create one': 'Buat satu', 'Create Tag': 'Buat Tag', 'Current thumbnail': 'Gambar mini saat ini',
  'Day Milestone': 'Pencapaian Hari', 'Default': 'Default', 'Domain': 'Domain', 'Download Submission': 'Unduh Kiriman',
  'Earned achievements': 'Pencapaian yang Diperoleh', 'Edit Assignment': 'Ubah Tugas', 'Edit Badge': 'Ubah Lencana',
  'Edit Category': 'Ubah Kategori', 'Edit Course': 'Ubah Kursus', 'Edit Discussion': 'Ubah Diskusi',
  'Edit Learning Path': 'Ubah Jalur Pembelajaran', 'Edit Tag': 'Ubah Tag', 'Edit Tenant': 'Ubah Tenant', 'Edit User': 'Ubah Pengguna',
  'Email:': 'Email:', 'Exam Monitor': 'Pemantauan Ujian', 'Exam Summary': 'Ringkasan Ujian', 'Exam:': 'Ujian:',
  'Force Submit': 'Paksa Kirim', 'Gamification Settings': 'Pengaturan Gamifikasi', 'Grade Submission': 'Nilai Kiriman',
  'Grade:': 'Nilai:', 'Import CSV': 'Impor CSV', 'Join': 'Gabung', 'Main Logo': 'Logo Utama',
  'Manage Content': 'Kelola Konten', 'Manage course tags': 'Kelola tag kursus', 'Materials': 'Materi',
  'Max File Size:': 'Ukuran Berkas Maksimal:', 'Max Score:': 'Nilai Maksimal:', 'Monitor Exam': 'Pantau Ujian',
  'My Gamification': 'Gamifikasi Saya', 'My Grades': 'Nilai Saya', 'New Assignment': 'Tugas Baru',
  'No assignments yet': 'Belum ada tugas', 'No assignments yet.': 'Belum ada tugas.', 'No audit logs found': 'Log audit tidak ditemukan',
  'No categories yet': 'Belum ada kategori', 'No course data': 'Data kursus belum tersedia', 'No courses added yet': 'Belum ada kursus yang ditambahkan',
  'No courses yet': 'Belum ada kursus', 'No data available yet.': 'Data belum tersedia.', 'No discussions yet.': 'Belum ada diskusi.',
  'No domain': 'Tidak ada domain', 'No learning paths yet': 'Belum ada jalur pembelajaran', 'No logo uploaded': 'Logo belum diunggah',
  'No quizzes yet.': 'Belum ada kuis.', 'No sections yet': 'Belum ada bagian', 'No students enrolled': 'Belum ada siswa yang terdaftar',
  'No submissions yet.': 'Belum ada kiriman.', 'No tags yet': 'Belum ada tag', 'No tenants yet': 'Belum ada tenant', 'No users found': 'Pengguna tidak ditemukan',
  'Not Eligible': 'Belum Memenuhi Syarat', 'Notes:': 'Catatan:', 'Password:': 'Kata Sandi:', 'PDF Preview': 'Pratinjau PDF',
  'Points': 'Poin', 'Post a Reply': 'Tulis Balasan', 'Post Reply': 'Kirim Balasan', 'Properties': 'Properti',
  'public': 'publik', 'Quiz Grading': 'Penilaian Kuis', 'Remove': 'Hapus', 'Reviews': 'Ulasan',
  'Role must be': 'Peran harus berupa', 'Save Branding': 'Simpan Identitas Merek', 'Save Levels': 'Simpan Level',
  'Save Milestones': 'Simpan Pencapaian', 'Save Settings': 'Simpan Pengaturan', 'Save XP Config': 'Simpan Pengaturan XP',
  'Score Distribution': 'Distribusi Nilai', 'Sections & Lessons': 'Bagian & Pelajaran', 'Sections:': 'Bagian:',
  'Set how many XP points are awarded for each action.': 'Atur jumlah poin XP yang diberikan untuk setiap tindakan.',
  'Start Discussion': 'Mulai Diskusi', 'Status:': 'Status:', 'Student enrollment & progress tracking': 'Pendaftaran dan pemantauan progres siswa',
  'Student Progress': 'Progres Siswa', 'Student Rankings': 'Peringkat Siswa', 'Student:': 'Siswa:',
  'Students Enrolled:': 'Siswa Terdaftar:', 'Submission Details': 'Detail Kiriman', 'Submit Grade': 'Kirim Nilai',
  'Tab switches:': 'Perpindahan tab:', 'Total': 'Total', 'Total Assignments': 'Total Tugas', 'Total Enrolled': 'Total Terdaftar',
  'Total Lessons': 'Total Pelajaran', 'Total Paths': 'Total Jalur', 'Total Quizzes': 'Total Kuis',
  'Total Sessions:': 'Total Sesi:', 'Total Students': 'Total Siswa', 'Total:': 'Total:',
  'Track all system activities': 'Pantau semua aktivitas sistem', 'Upcoming Sessions': 'Sesi Mendatang',
  'Update Assignment': 'Perbarui Tugas', 'Update Badge': 'Perbarui Lencana', 'Update Discussion': 'Perbarui Diskusi',
  'Update Path': 'Perbarui Jalur', 'Update Tenant': 'Perbarui Tenant', 'Update User': 'Perbarui Pengguna',
  'Upload Material': 'Unggah Materi', 'Upload Submission': 'Unggah Kiriman', 'Ready for Processing': 'Siap Diproses',
  'New course is created as': 'Kursus baru dibuat dengan status', 'Complete': 'Selesai', 'Upcoming': 'Mendatang',
  'Approved': 'Disetujui', 'Rejected': 'Ditolak', 'Pending Approval': 'Menunggu Persetujuan', 'Gradebook': 'Buku Nilai', 'Gradebook': 'Buku Nilai', 'Grades Overview': 'Ringkasan Nilai',
  'No grades available for this course.': 'Nilai untuk kursus ini belum tersedia.',
  'Enroll in courses to start tracking your grades.': 'Ikuti kursus untuk mulai melihat nilai Anda.',
  'Download Transcript (PDF)': 'Unduh Transkrip (PDF)', 'Courses': 'Kursus', 'Enrollments': 'Pendaftaran', 'Hide Completed': 'Sembunyikan yang Selesai', 'Show Completed': 'Tampilkan yang Selesai', 'No courses found.': 'Kursus tidak ditemukan.',
  'Enroll Now': 'Daftar Sekarang', 'Enroll to Get Started': 'Daftar untuk Memulai', 'Continue': 'Lanjutkan', 'Start Over': 'Mulai Ulang', 'Start Learning': 'Mulai Belajar',
  'Enroll in this learning path to start learning!': 'Daftar ke jalur pembelajaran ini untuk mulai belajar!',
  'Start Course': 'Mulai Kursus', 'Continue Learning': 'Lanjutkan Belajar', 'Continue learning this lesson using the video above.': 'Lanjutkan pelajaran ini melalui video di atas.',
  'Explore Courses': 'Jelajahi Kursus', 'Browse Courses': 'Telusuri Kursus', 'Explore Learning Paths': 'Jelajahi Jalur Pembelajaran',
  'No Learning Paths Available': 'Belum Ada Jalur Pembelajaran', 'Check back soon for new learning paths!': 'Kunjungi kembali nanti untuk melihat jalur pembelajaran baru!',
  'Start a learning path to track your progress': 'Mulai jalur pembelajaran untuk memantau progres Anda', 'From Address (email)': 'Alamat Pengirim (email)', 'From Name': 'Nama Pengirim', 'SMTP Host': 'Host SMTP', 'SMTP Port': 'Port SMTP', 'SMTP Username': 'Nama Pengguna SMTP', 'SMTP Password': 'Kata Sandi SMTP',
  'Track your progress and continue learning': 'Pantau progres dan lanjutkan pembelajaran Anda', 'Welcome back,': 'Selamat datang kembali, ', 'Username': 'Nama Pengguna', 'Email': 'Email', 'Password': 'Kata Sandi', 'Confirm Password': 'Konfirmasi Kata Sandi', 'Remember Me': 'Ingat Saya', 
  'Forgot Password?': 'Lupa Kata Sandi?', 'Login': 'Masuk', 'Register': 'Daftar', 'Logout': 'Keluar', 'Dashboard': 'Dasbor', 'Profile': 'Profil', 'Settings': 'Pengaturan', 'Help': 'Bantuan', 'Search': 'Cari',
  'All Paths': 'Semua Jalur', 'Next Course': 'Kursus Berikutnya', 'Courses Completed': 'Kursus Selesai', 'What You\'ll Learn': 'Yang Akan Anda Pelajari', 
  'Key concepts and fundamental knowledge': 'Konsep utama dan pengetahuan dasar', 'Practical application of the concepts': 'Penerapan konsep secara praktis', 
  'Best practices and industry standards': 'Praktik terbaik dan standar industri', 'Hands-on exercises and examples': 'Latihan praktik dan contoh', 'Structured curriculum to master new skills': 'Kurikulum terstruktur untuk menguasai keterampilan baru', 
  'Learning Outcomes': 'Hasil Pembelajaran', 'Course Title': 'Judul Kursus', 'Course Description': 'Deskripsi Kursus', 'Course Category': 'Kategori Kursus', 'Course Level': 'Tingkat Kursus', 'Course URL Slug': 'Slug URL Kursus', 
  'Course Language': 'Bahasa Kursus', 'Course Price': 'Harga Kursus', 'Course Thumbnail': 'Gambar Mini Kursus', 'Course Status': 'Status Kursus', 'Course Visibility': 'Visibilitas Kursus', 'Replace Thumbnail': 'Ganti Gambar Mini', 
  'Remove Thumbnail': 'Hapus Gambar Mini', 'Upload Thumbnail': 'Unggah Gambar Mini', 'Course Settings': 'Pengaturan Kursus', 'Course Content': 'Konten Kursus', 'Add Section': 'Tambah Bagian', '+ Add Lesson': '+ Tambah Pelajaran', 'Lesson Title': 'Judul Pelajaran', 
  'Add Quiz': 'Tambah Kuis', 'Add Assignment': 'Tambah Tugas', 'Add Material': 'Tambah Materi', 'Add Discussion': 'Tambah Diskusi', 'Lessons': 'Pelajaran', 'Quizzes': 'Kuis', 'Assignments': 'Tugas', 'Materials': 'Materi', 'Discussions': 'Diskusi',
  'Edit Section': 'Ubah Bagian', 'Edit Lesson': 'Ubah Pelajaran', 'Edit Quiz': 'Ubah Kuis', 'Edit Assignment': 'Ubah Tugas', 'Edit Material': 'Ubah Materi', 'Edit Discussion': 'Ubah Diskusi',
  'Delete Section': 'Hapus Bagian', 'Delete Lesson': 'Hapus Pelajaran', 'Delete Quiz': 'Hapus Kuis', 'Delete Assignment': 'Hapus Tugas', 'Delete Material': 'Hapus Materi', 'Delete Discussion': 'Hapus Diskusi', 'Items per page:': 'Item per halaman:', 'Page': 'Halaman', 'of': 'dari', 'No items found.': 'Tidak ada item yang ditemukan.', 'Loading...': 'Memuat...', 'Search...': 'Cari...', 'Filter by:': 'Filter berdasarkan:', 'Sort by:': 'Urutkan berdasarkan:',
  'Save the settings first, then choose where the SMTP test email should be sent.': 'Simpan pengaturan terlebih dahulu, lalu pilih ke mana email uji SMTP harus dikirim.',
  'Send Test Email': 'Kirim Email Uji', 'Test Email Sent': 'Email Uji Terkirim', 'Test Email Failed': 'Email Uji Gagal Dikirim', 'Please check your SMTP settings and try again.': 'Periksa pengaturan SMTP Anda dan coba lagi.',
  'Test Email Sent Successfully': 'Email Uji Berhasil Dikirim', 'Test Email Failed to Send': 'Email Uji Gagal Dikirim', 'Please check your SMTP settings and try again.': 'Periksa pengaturan SMTP Anda dan coba lagi.',
  'SMTP Test Email': 'Email Uji SMTP', 'SMTP Test Email Sent': 'Email Uji SMTP Terkirim', 'SMTP Test Email Failed': 'Email Uji SMTP Gagal Dikirim', 'Please check your SMTP settings and try again.': 'Periksa pengaturan SMTP Anda dan coba lagi.',
  'Send Test Email to My Account': 'Kirim Email Uji ke Akun Saya', 'Send Test Email To Another Address': 'Kirim Email Uji ke Alamat Lain', 'Enter Email Address': 'Masukkan Alamat Email', 'Send Test Email': 'Kirim Email Uji', 'Test Email Sent Successfully': 'Email Uji Berhasil Dikirim', 'Test Email Failed to Send': 'Email Uji Gagal Dikirim', 'Please check your SMTP settings and try again.': 'Periksa pengaturan SMTP Anda dan coba lagi.',
  'Leave blank to keep the saved password. Gmail requires an App Password, not your normal Google password.': 'Kosongkan untuk mempertahankan kata sandi yang tersimpan. Gmail memerlukan Kata Sandi Aplikasi, bukan kata sandi Google biasa Anda.',
})

const readInitialLanguage = (storageKey = activeStorageKey, cookieKey = activeCookieKey) => {
  try {
    const stored = localStorage.getItem(storageKey)
    if (supportedLanguages.includes(stored)) return stored
    const cookie = document.cookie.split('; ').find(row => row.startsWith(`${cookieKey}=`))
    const fromCookie = cookie?.split('=')[1]
    return supportedLanguages.includes(fromCookie) ? fromCookie : 'id'
  } catch {
    return 'id'
  }
}

export const locale = ref(typeof window === 'undefined' ? 'id' : readInitialLanguage())
export const setLanguageScope = scope => {
  const tenantId = typeof scope === 'string' && scope.startsWith('tenant:')
    ? scope.slice('tenant:'.length).replace(/[^A-Za-z0-9_-]/g, '_')
    : ''

  activeStorageKey = tenantId ? `${STORAGE_KEY}.tenant.${tenantId}` : STORAGE_KEY
  activeCookieKey = tenantId ? `${COOKIE_KEY}_tenant_${tenantId}` : COOKIE_KEY
  locale.value = readInitialLanguage(activeStorageKey, activeCookieKey)
}

export const languageOptions = [
  { code: 'id', label: '🇮🇩 Bahasa Indonesia' },
  { code: 'en', label: '🇬🇧 English' },
]

watch(locale, value => {
  if (!supportedLanguages.includes(value)) return
  if (typeof document !== 'undefined') {
    document.documentElement.lang = value
    document.cookie = `${activeCookieKey}=${value}; path=/; max-age=31536000; samesite=lax`
  }
  try { localStorage.setItem(activeStorageKey, value) } catch { /* storage may be disabled */ }
}, { immediate: true })

export const useLanguage = () => ({
  locale,
  languageOptions,
  isIndonesian: computed(() => locale.value === 'id'),
  setLanguage: value => { if (supportedLanguages.includes(value)) locale.value = value },
  t: value => locale.value === 'id' ? (translations.id[value] ?? value) : value,
})

const translateText = value => {
  if (!value.trim()) return value
  const leading = value.match(/^\s*/)?.[0] ?? ''
  const trailing = value.match(/\s*$/)?.[0] ?? ''
  const body = value.trim()
  let translated = translations.id[body]

  if (!translated) {
    const dynamicTranslations = [
      [/^Video is processing \((.+)\)$/, (_, status) => `Video sedang diproses (${status})`],
      [/^Started (.+)$/, (_, date) => `Dimulai ${date}`],
      [/^Completed on (.+)$/, (_, date) => `Selesai pada ${date}`],
      [/^Issued (.+)$/, (_, date) => `Diterbitkan ${date}`],
      [/^Deadline: (.+)$/, (_, date) => `Batas waktu: ${date}`],
      [/^Max Score: (.+)$/, (_, score) => `Nilai Maksimal: ${score}`],
      [/^Passing: (.+)$/, (_, score) => `Nilai Kelulusan: ${score}`],
      [/^Pass: (.+)$/, (_, score) => `Lulus: ${score}`],
      [/^Questions \((.+)\)$/, (_, count) => `Pertanyaan (${count})`],
      [/^Replies \((.+)\)$/, (_, count) => `Balasan (${count})`],
      [/^Submissions \((.+)\)$/, (_, count) => `Kiriman (${count})`],
      [/^\+ (\d+) more courses$/, (_, count) => `+ ${count} kursus lainnya`],
      [/^Longest: (.+) days$/, (_, days) => `Terpanjang: ${days} hari`],
      [/^Attempt #(\d+)$/, (_, attempt) => `Pengerjaan ke-${attempt}`],
      [/^Question (\d+)$/, (_, number) => `Pertanyaan ${number}`],
      [/^result(s?) for "(.+)"$/, (_, plural, query) => `hasil untuk "${query}"`],
      [/^Student: (.+) — Submitted: (.+)$/, (_, student, date) => `Siswa: ${student} — Dikirim: ${date}`],
      [/^Are you sure you want to delete "(.+)"\?$/, (_, name) => `Apakah Anda yakin ingin menghapus "${name}"?`],
      [/^Delete badge "(.+)"\?$/, (_, name) => `Hapus lencana "${name}"?`],
      [/^Delete tag "(.+)"\?$/, (_, name) => `Hapus tag "${name}"?`],
      [/^Delete "(.+)"\? This will remove all data\.$/, (_, name) => `Hapus "${name}"? Semua data tenant ini akan dihapus.`],
      [/^Delete course "(.+)"\?$/, (_, name) => `Hapus kursus "${name}"?`],
      [/^Reset "(.+)" to default value\?$/, (_, name) => `Atur ulang "${name}" ke nilai awal?`],
      [/^Force submit attempt #(\d+)\?$/, (_, number) => `Paksa kirim pengerjaan ke-${number}?`],
      [/^Please complete the required field: (.+)$/, (_, fields) => `Lengkapi kolom yang wajib diisi: ${fields}`],
      [/^Please complete the required fields: (.+)$/, (_, fields) => `Lengkapi kolom-kolom yang wajib diisi: ${fields}`],
      [/^You have switched tabs too many times\. Your quiz will be submitted\.$/, () => 'Anda terlalu sering berpindah tab. Jawaban kuis akan dikirim.'],
      [/^Are you sure you want to delete this section\?$/, () => 'Apakah Anda yakin ingin menghapus bagian ini?'],
      [/^Are you sure you want to delete this lesson\?$/, () => 'Apakah Anda yakin ingin menghapus pelajaran ini?'],
      [/^Are you sure you want to delete this video\?$/, () => 'Apakah Anda yakin ingin menghapus video ini?'],
      [/^Are you sure you want to delete this material\?$/, () => 'Apakah Anda yakin ingin menghapus materi ini?'],
    ]
    const match = dynamicTranslations.find(([pattern]) => pattern.test(body))
    translated = match ? body.replace(match[0], match[1]) : body
  }

  return `${leading}${translated}${trailing}`
}

// Translate visible UI text across Inertia pages, including content added by dialogs/snackbars.
// Exact phrase matching avoids changing course names and other user supplied content.
export const installPageTranslations = () => {
  if (typeof document === 'undefined' || !document.body) return
  if (typeof window !== 'undefined' && !window.__kitsuluhLanguageDialogs) {
    window.__kitsuluhLanguageDialogs = true
    const nativeAlert = window.alert.bind(window)
    const nativeConfirm = window.confirm.bind(window)
    window.alert = message => nativeAlert(locale.value === 'id' ? translateText(String(message)) : message)
    window.confirm = message => nativeConfirm(locale.value === 'id' ? translateText(String(message)) : message)
  }
  const originalText = new WeakMap()
  const originalAttributes = new WeakMap()
  const translateNode = node => {
    if (node.nodeType === Node.TEXT_NODE) {
      const value = node.nodeValue
      const previous = originalText.get(node)
      if (locale.value === 'id') {
        if (previous?.translated === value) return
        const translated = translateText(value)
        if (translated !== value) {
          originalText.set(node, { original: value, translated })
          node.nodeValue = translated
        }
      } else if (previous) {
        node.nodeValue = previous.translated === value ? previous.original : value
        originalText.delete(node)
      }
    } else if (node.nodeType === Node.ELEMENT_NODE) {
      const saved = originalAttributes.get(node) ?? {}
      for (const attr of ['placeholder', 'title', 'aria-label']) {
        const value = node.getAttribute(attr)
        if (!value) continue
        if (locale.value === 'id') {
          if (saved[attr]?.translated === value) continue
          const translated = translateText(value)
          if (translated !== value) {
            saved[attr] = { original: value, translated }
            node.setAttribute(attr, translated)
          }
        } else if (saved[attr]) {
          node.setAttribute(attr, saved[attr].translated === value ? saved[attr].original : value)
          delete saved[attr]
        }
      }
      if (Object.keys(saved).length) originalAttributes.set(node, saved)
      else originalAttributes.delete(node)
    }
  }
  const scan = root => {
    translateNode(root)
    root.querySelectorAll?.('*').forEach(translateNode)
    const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT)
    let node
    while ((node = walker.nextNode())) translateNode(node)
  }
  scan(document.body)
  const observer = new MutationObserver(records => records.forEach(record => {
    record.addedNodes.forEach(scan)
    if (record.type === 'characterData') translateNode(record.target)
    if (record.type === 'attributes') translateNode(record.target)
  }))
  observer.observe(document.body, { subtree: true, childList: true, characterData: true, attributes: true, attributeFilter: ['placeholder', 'title', 'aria-label'] })
  watch(locale, () => scan(document.body))
}
