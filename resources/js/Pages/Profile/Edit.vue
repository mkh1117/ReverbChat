<template>
  <div class="max-w-4xl mx-auto p-4 sm:p-6 space-y-6" dir="rtl">

    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 space-y-6">
      <h3 class="text-base font-bold text-gray-800 border-b pb-3">تصویر پروفایل</h3>

      <div class="flex flex-col sm:flex-row items-center gap-6">
        <div class="relative w-28 h-28 rounded-full overflow-hidden border-4 border-blue-500/20 shadow-md flex-shrink-0">
          <img v-if="user.current_avatar" :src="user.current_avatar" class="w-full h-full object-cover" />
          <div v-else class="w-full h-full bg-blue-600 text-white font-bold text-3xl flex items-center justify-center">
            {{ user.name ? user.name.charAt(0) : 'U' }}
          </div>
        </div>

        <div class="space-y-3 text-center sm:text-right">
          <div>
            <label class="cursor-pointer bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2.5 rounded-xl transition-all inline-block shadow-md">
              <span>آپلود تصویر جدید</span>
              <input type="file" ref="fileInput" class="hidden" accept="image/*" @change="handleAvatarUpload" />
            </label>
          </div>
          <p class="text-[11px] text-gray-400">فرمت‌های مجاز: JPG, PNG, WEBP (حداکثر ۴ مگابایت)</p>
        </div>
      </div>

      <!-- تاریخچه آواتارها -->
      <div v-if="user.avatars && user.avatars.length > 0" class="space-y-2 pt-2 border-t">
        <span class="text-xs font-semibold text-gray-500">تصاویر قبلی</span>
        <div class="flex flex-wrap gap-3">
          <div v-for="avatar in user.avatars" :key="avatar.id" class="relative group w-14 h-14 rounded-2xl overflow-hidden border border-gray-200">
            <img :src="avatar.url" class="w-full h-full object-cover" />
            <button
              @click="deleteAvatar(avatar.id)"
              class="absolute inset-0 bg-red-600/70 opacity-0 group-hover:opacity-100 text-white flex items-center justify-center transition-opacity"
              title="حذف تصویر"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    
    <form @submit.prevent="updateProfile" class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 space-y-4">
      <h3 class="text-base font-bold text-gray-800 border-b pb-3">اطلاعات حساب کاربری</h3>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">نام و نام خانوادگی</label>
          <input v-model="profileForm.name" type="text" class="w-full text-xs bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none" />
          <p v-if="profileForm.errors.name" class="text-xs text-red-500 mt-1">{{ profileForm.errors.name }}</p>
        </div>

        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">نام کاربری (Username)</label>
          <input v-model="profileForm.username" type="text" dir="ltr" class="w-full text-xs bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none text-left" />
          <p v-if="profileForm.errors.username" class="text-xs text-red-500 mt-1">{{ profileForm.errors.username }}</p>
        </div>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">بیوگرافی (توضیحات)</label>
        <textarea v-model="profileForm.bio" rows="3" class="w-full text-xs bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="درباره خودتان بنویسید..."></textarea>
        <p v-if="profileForm.errors.bio" class="text-xs text-red-500 mt-1">{{ profileForm.errors.bio }}</p>
      </div>

      <div class="flex justify-end pt-2">
        <button type="submit" :disabled="profileForm.processing" class="px-5 py-2.5 text-xs bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl shadow-md disabled:opacity-50 transition-all">
          {{ profileForm.processing ? 'در حال ذخیره...' : 'ذخیره تغییرات' }}
        </button>
      </div>
    </form>


    <form @submit.prevent="updatePassword" class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 space-y-4">
      <h3 class="text-base font-bold text-gray-800 border-b pb-3">تغییر رمز عبور</h3>

      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">رمز عبور فعلی</label>
        <input v-model="passwordForm.current_password" type="password" class="w-full text-xs bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none" />
        <p v-if="passwordForm.errors.current_password" class="text-xs text-red-500 mt-1">{{ passwordForm.errors.current_password }}</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">رمز عبور جدید</label>
          <input v-model="passwordForm.password" type="password" class="w-full text-xs bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none" />
          <p v-if="passwordForm.errors.password" class="text-xs text-red-500 mt-1">{{ passwordForm.errors.password }}</p>
        </div>

        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">تکرار رمز عبور جدید</label>
          <input v-model="passwordForm.password_confirmation" type="password" class="w-full text-xs bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none" />
        </div>
      </div>

      <div class="flex justify-end pt-2">
        <button type="submit" :disabled="passwordForm.processing" class="px-5 py-2.5 text-xs bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl shadow-md disabled:opacity-50 transition-all">
          {{ passwordForm.processing ? 'در حال به روزرسانی...' : 'تغییر رمز عبور' }}
        </button>
      </div>
    </form>

  </div>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3'

const props = defineProps({
  user: Object
})

const profileForm = useForm({
  name: props.user.name || '',
  username: props.user.username || '',
  bio: props.user.bio || '',
})

const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const updateProfile = () => {
  profileForm.post('/profile', { preserveScroll: true })
}

const updatePassword = () => {
  passwordForm.post('/profile/password', {
    preserveScroll: true,
    onSuccess: () => passwordForm.reset(),
  })
}

const handleAvatarUpload = (e) => {
  const file = e.target.files[0]
  if (!file) return

  const formData = new FormData()
  formData.append('avatar', file)

  router.post('/profile/avatar', formData, {
    preserveScroll: true,
  })
}

const deleteAvatar = (id) => {
  if (confirm('آیا از حذف این تصویر اطمینان دارید؟')) {
    router.delete(`/profile/avatar/${id}`, { preserveScroll: true })
  }
}
</script>
