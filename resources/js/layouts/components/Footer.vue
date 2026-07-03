<template>
  <div class="h-100 d-flex align-center justify-md-space-between justify-center">
    <span class="d-flex align-center text-medium-emphasis">
      &copy; {{ new Date().getFullYear() }} KITsuluh PT Krakatau Information Technology
    </span>
    <span class="d-md-flex gap-x-4 text-primary d-none">
      <a
        :href="route('dashboard')"
        class="text-primary ms-1"
      >Dashboard</a>
      <a
        :href="route('notifications.index')"
        class="text-primary ms-1"
      >Notifications</a>
    </span>
  </div>
</template>
