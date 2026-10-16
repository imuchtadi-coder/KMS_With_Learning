<script setup>
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
  tenants: {
    type: Object,
    default: () => ({ data: [] }),
  },
  counts: {
    type: Object,
    default: () => ({ all: 0, pending: 0, active: 0, rejected: 0 }),
  },
  status: {
    type: String,
    default: 'all',
  },
})

// Proof Modal State
const isProofDialogOpen = ref(false)
const selectedTenant = ref(null)

const openProofDialog = (tenant) => {
  selectedTenant.value = tenant
  isProofDialogOpen.value = true
}

const formatRupiah = (val) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(val || 0)
}

const filterByStatus = (status) => {
  router.get(route('admin.tenants.index'), { status }, { preserveState: true })
}

const approveTenant = (tenant) => {
  if (!confirm(`Setujui pendaftaran tenant "${tenant.name}"? Pembayaran akan diverifikasi dan tenant langsung aktif.`)) {
    return
  }
  router.post(route('admin.tenants.approve', tenant.id), {}, {
    preserveScroll: true,
    onSuccess: () => {
      isProofDialogOpen.value = false
    },
  })
}

const rejectTenant = (tenant) => {
  if (!confirm(`Tolak pendaftaran tenant "${tenant.name}"?`)) {
    return
  }
  router.post(route('admin.tenants.reject', tenant.id), {}, {
    preserveScroll: true,
    onSuccess: () => {
      isProofDialogOpen.value = false
    },
  })
}

const deleteTenant = (tenant) => {
  if (!confirm(`Hapus "${tenant.name}"? Ini akan menghapus seluruh data yang terkait.`)) {
    return
  }
  router.delete(route('admin.tenants.delete', tenant.id), { preserveScroll: true })
}

const getStatusColor = (status) => {
  if (status === 'active') return 'success'
  if (status === 'pending') return 'warning'
  if (status === 'rejected') return 'error'
  return 'secondary'
}

const getStatusLabel = (status) => {
  if (status === 'active') return 'Aktif'
  if (status === 'pending') return 'Menunggu Approval'
  if (status === 'rejected') return 'Ditolak'
  return status || 'Aktif'
}

const getPaymentStatusColor = (status) => {
  if (status === 'verified') return 'success'
  if (status === 'pending') return 'warning'
  if (status === 'rejected') return 'error'
  return 'secondary'
}

const getPaymentStatusLabel = (status) => {
  if (status === 'verified') return 'Lunas'
  if (status === 'pending') return 'Menunggu Verifikasi'
  if (status === 'rejected') return 'Ditolak'
  return status || 'Belum Bayar'
}
</script>

<template>
  <div>
    <!-- Page Header -->
    <div class="d-flex flex-wrap align-center justify-space-between gap-4 mb-6">
      <div>
        <h4 class="text-h4 font-weight-bold mb-1">
          Manajemen Tenant & Langganan
        </h4>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Kelola institusi, verifikasi bukti transaksi e-Wallet, paket langganan, dan persetujuan registrasi (Khusus Superadmin)
        </p>
      </div>

      <div class="d-flex align-center ga-2">
        <VBtn
          color="secondary"
          variant="tonal"
          prepend-icon="mdi-tag-multiple-outline"
          :href="route('admin.subscription-plans.index')"
        >
          Kelola Paket & Harga
        </VBtn>

        <VBtn
          color="primary"
          variant="elevated"
          prepend-icon="mdi-plus"
          :href="route('admin.tenants.create')"
        >
          Tambah Tenant
        </VBtn>
      </div>
    </div>

    <!-- Status Filter Tabs / Chips -->
    <VCard flat border class="mb-6">
      <VCardText class="pa-4">
        <div class="d-flex flex-wrap align-center ga-2">
          <VChip
            :color="status === 'all' ? 'primary' : 'default'"
            :variant="status === 'all' ? 'elevated' : 'tonal'"
            class="cursor-pointer"
            @click="filterByStatus('all')"
          >
            Semua
            <VBadge
              :content="counts.all"
              inline
              color="white"
              text-color="primary"
              class="ml-2"
            />
          </VChip>

          <VChip
            :color="status === 'pending' ? 'warning' : 'default'"
            :variant="status === 'pending' ? 'elevated' : 'tonal'"
            class="cursor-pointer"
            @click="filterByStatus('pending')"
          >
            <VIcon start icon="mdi-clock-outline" />
            Menunggu Persetujuan
            <VBadge
              v-if="counts.pending > 0"
              :content="counts.pending"
              inline
              color="error"
              class="ml-2"
            />
            <span v-else class="ml-1 text-caption">({{ counts.pending }})</span>
          </VChip>

          <VChip
            :color="status === 'active' ? 'success' : 'default'"
            :variant="status === 'active' ? 'elevated' : 'tonal'"
            class="cursor-pointer"
            @click="filterByStatus('active')"
          >
            <VIcon start icon="mdi-check-circle-outline" />
            Aktif
            <span class="ml-1 text-caption">({{ counts.active }})</span>
          </VChip>

          <VChip
            :color="status === 'rejected' ? 'error' : 'default'"
            :variant="status === 'rejected' ? 'elevated' : 'tonal'"
            class="cursor-pointer"
            @click="filterByStatus('rejected')"
          >
            <VIcon start icon="mdi-close-circle-outline" />
            Ditolak
            <span class="ml-1 text-caption">({{ counts.rejected }})</span>
          </VChip>
        </div>
      </VCardText>
    </VCard>

    <!-- Tenants Table -->
    <VCard flat border>
      <VTable density="comfortable">
        <thead>
          <tr>
            <th>ID / Subdomain</th>
            <th>Institusi</th>
            <th>Paket Langganan</th>
            <th>Pembayaran e-Wallet</th>
            <th>Kontak / PIC</th>
            <th>Status Akun</th>
            <th>Tgl Daftar</th>
            <th class="text-right">Aksi</th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="tenant in tenants.data"
            :key="tenant.id"
          >
            <td class="text-caption font-family-mono font-weight-bold">
              {{ tenant.id }}
            </td>

            <td>
              <div class="font-weight-medium text-body-2">
                {{ tenant.name }}
              </div>
              <div v-if="tenant.domains?.length" class="text-caption text-primary">
                {{ tenant.domains[0].domain }}
              </div>
            </td>

            <!-- Paket Langganan & Biaya -->
            <td>
              <div class="d-flex flex-column align-start ga-1">
                <VChip
                  size="small"
                  color="primary"
                  variant="flat"
                  class="font-weight-bold"
                >
                  {{ tenant.plan?.name || 'Paket Kustom' }}
                </VChip>
                <span class="text-caption font-weight-medium text-high-emphasis">
                  {{ formatRupiah(tenant.amount_paid || tenant.plan?.price) }}
                </span>
              </div>
            </td>

            <!-- Pembayaran e-Wallet & Bukti Transaksi -->
            <td>
              <div class="d-flex flex-column align-start ga-1">
                <div class="d-flex align-center ga-1">
                  <VChip
                    size="x-small"
                    variant="tonal"
                    color="secondary"
                    class="font-weight-bold text-uppercase"
                  >
                    {{ tenant.payment_method || 'e-Wallet' }}
                  </VChip>

                  <VChip
                    size="x-small"
                    :color="getPaymentStatusColor(tenant.payment_status)"
                    variant="tonal"
                    class="font-weight-medium"
                  >
                    {{ getPaymentStatusLabel(tenant.payment_status) }}
                  </VChip>
                </div>

                <!-- Tombol Lihat Bukti Bayar -->
                <VBtn
                  v-if="tenant.payment_proof"
                  size="x-small"
                  variant="outlined"
                  color="info"
                  prepend-icon="mdi-receipt-text-outline"
                  class="mt-1"
                  @click="openProofDialog(tenant)"
                >
                  Lihat Bukti Bayar
                </VBtn>
                <span v-else class="text-caption text-disabled">-</span>
              </div>
            </td>

            <td>
              <div class="text-body-2">
                {{ tenant.contact_person || (tenant.users && tenant.users[0]?.name) || '-' }}
              </div>
              <div class="text-caption text-medium-emphasis">
                {{ tenant.phone || (tenant.users && tenant.users[0]?.email) || '-' }}
              </div>
            </td>

            <td>
              <VChip
                :color="getStatusColor(tenant.status)"
                size="small"
                variant="tonal"
                class="font-weight-medium"
              >
                {{ getStatusLabel(tenant.status) }}
              </VChip>
            </td>

            <td class="text-caption">
              {{ new Date(tenant.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) }}
            </td>

            <td class="text-right">
              <div class="d-inline-flex align-center ga-1">
                <!-- Approve Button for Pending/Rejected -->
                <VBtn
                  v-if="tenant.status === 'pending' || tenant.status === 'rejected'"
                  size="small"
                  color="success"
                  variant="flat"
                  prepend-icon="mdi-check"
                  class="text-capitalize"
                  @click="approveTenant(tenant)"
                >
                  Setujui
                </VBtn>

                <!-- Reject Button for Pending -->
                <VBtn
                  v-if="tenant.status === 'pending'"
                  size="small"
                  color="error"
                  variant="tonal"
                  prepend-icon="mdi-close"
                  class="text-capitalize"
                  @click="rejectTenant(tenant)"
                >
                  Tolak
                </VBtn>

                <!-- Edit -->
                <VBtn
                  icon
                  size="small"
                  color="warning"
                  variant="text"
                  :href="route('admin.tenants.edit', tenant.id)"
                  title="Edit Tenant"
                >
                  <VIcon icon="mdi-pencil-outline" size="18" />
                </VBtn>

                <!-- Delete -->
                <VBtn
                  icon
                  size="small"
                  color="error"
                  variant="text"
                  title="Hapus Tenant"
                  @click="deleteTenant(tenant)"
                >
                  <VIcon icon="mdi-delete-outline" size="18" />
                </VBtn>
              </div>
            </td>
          </tr>

          <tr v-if="!tenants.data.length">
            <td colspan="8" class="text-center py-8 text-medium-emphasis">
              <VIcon icon="mdi-domain-off" size="48" class="mb-2 text-disabled" />
              <div>Belum ada data tenant pada filter ini.</div>
            </td>
          </tr>
        </tbody>
      </VTable>

      <!-- Pagination -->
      <VDivider v-if="tenants.total > tenants.per_page" />
      <div
        v-if="tenants.total > tenants.per_page"
        class="pa-4 d-flex align-center justify-space-between flex-wrap gap-2"
      >
        <span class="text-body-2 text-medium-emphasis">
          Menampilkan {{ tenants.from }} sampai {{ tenants.to }} dari total {{ tenants.total }} tenant
        </span>
        <VPagination
          :model-value="tenants.current_page"
          :length="tenants.last_page"
          :total-visible="5"
          density="comfortable"
          @update:model-value="
            val =>
              router.get(
                route('admin.tenants.index', { status: status, page: val })
              )
          "
        />
      </div>
    </VCard>

    <!-- ================= MODAL PREVIEW BUKTI PEMBAYARAN E-WALLET ================= -->
    <VDialog v-model="isProofDialogOpen" max-width="650">
      <VCard v-if="selectedTenant" class="rounded-xl overflow-hidden">
        <VCardTitle class="pa-6 border-b d-flex align-center justify-space-between bg-surface-variant">
          <div class="d-flex align-center ga-2">
            <VIcon icon="mdi-receipt-text-check-outline" color="primary" />
            <span class="text-h6 font-weight-bold">
              Verifikasi Pembayaran: {{ selectedTenant.name }}
            </span>
          </div>
          <VBtn icon variant="text" size="small" @click="isProofDialogOpen = false">
            <VIcon icon="mdi-close" />
          </VBtn>
        </VCardTitle>

        <VCardText class="pa-6">
          <VRow>
            <!-- Detail Ringkasan Pembayaran -->
            <VCol cols="12">
              <div class="pa-4 rounded-lg bg-surface-container border mb-4">
                <div class="d-flex justify-space-between flex-wrap mb-2">
                  <span class="text-caption text-medium-emphasis">Paket Langganan:</span>
                  <VChip size="small" color="primary" variant="flat" class="font-weight-bold">
                    {{ selectedTenant.plan?.name || 'Paket Standar' }}
                  </VChip>
                </div>

                <div class="d-flex justify-space-between flex-wrap mb-2">
                  <span class="text-caption text-medium-emphasis">Nominal Tagihan:</span>
                  <span class="text-subtitle-1 font-weight-bold text-primary">
                    {{ formatRupiah(selectedTenant.amount_paid || selectedTenant.plan?.price) }}
                  </span>
                </div>

                <div class="d-flex justify-space-between flex-wrap mb-2">
                  <span class="text-caption text-medium-emphasis">Metode Pembayaran:</span>
                  <span class="text-body-2 font-weight-bold text-uppercase">
                    {{ selectedTenant.payment_method || 'e-Wallet' }}
                  </span>
                </div>

                <div class="d-flex justify-space-between flex-wrap">
                  <span class="text-caption text-medium-emphasis">Status Saat Ini:</span>
                  <VChip
                    size="x-small"
                    :color="getPaymentStatusColor(selectedTenant.payment_status)"
                    variant="tonal"
                    class="font-weight-bold"
                  >
                    {{ getPaymentStatusLabel(selectedTenant.payment_status) }}
                  </VChip>
                </div>
              </div>
            </VCol>

            <!-- Foto Bukti Transaksi -->
            <VCol cols="12" class="text-center">
              <div class="text-caption font-weight-bold text-uppercase text-medium-emphasis mb-2 text-left">
                Lampiran Bukti Transaksi e-Wallet:
              </div>

              <div class="border rounded-xl pa-2 bg-surface overflow-hidden text-center">
                <img
                  v-if="selectedTenant.payment_proof"
                  :src="`/storage/${selectedTenant.payment_proof}`"
                  alt="Bukti Transaksi e-Wallet"
                  class="img-fluid rounded-lg shadow-sm"
                  style="max-height: 400px; max-width: 100%; object-fit: contain;"
                />
                <div v-else class="py-8 text-disabled">
                  Tidak ada file bukti pembayaran yang diunggah.
                </div>
              </div>
            </VCol>
          </VRow>
        </VCardText>

        <VCardActions class="pa-6 border-t d-flex justify-space-between flex-wrap ga-2">
          <VBtn
            variant="text"
            color="secondary"
            @click="isProofDialogOpen = false"
          >
            Tutup
          </VBtn>

          <div class="d-flex align-center ga-2">
            <VBtn
              v-if="selectedTenant.status === 'pending'"
              color="error"
              variant="tonal"
              prepend-icon="mdi-close"
              @click="rejectTenant(selectedTenant)"
            >
              Tolak Pendaftaran
            </VBtn>

            <VBtn
              v-if="selectedTenant.status === 'pending' || selectedTenant.payment_status === 'pending'"
              color="success"
              variant="elevated"
              prepend-icon="mdi-check-decagram"
              class="font-weight-bold"
              @click="approveTenant(selectedTenant)"
            >
              Setujui & Verifikasi Pembayaran
            </VBtn>
          </div>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>

<style scoped>
.img-fluid {
  max-width: 100%;
  height: auto;
}
</style>