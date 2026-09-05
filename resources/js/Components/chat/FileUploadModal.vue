<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="close">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden flex flex-col transition-all dir-rtl" dir="rtl">

      <!-- هدر -->
      <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-bold text-gray-800">ارسال فایل</h3>
        <button @click="close" :disabled="isUploading" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg disabled:opacity-50">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>

      <!-- وضعیت موفقیت‌آمیز -->
      <div v-if="isSuccess" class="p-8 flex flex-col items-center justify-center bg-slate-50 min-h-[220px]">
        <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mb-3 animate-bounce">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <p class="text-sm font-bold text-gray-800">فایل با موفقیت ارسال شد</p>
      </div>

      <template v-else>
        <!-- پیش‌نمایش فایل -->
        <div class="p-5 flex flex-col items-center justify-center bg-slate-50 min-h-[180px] max-h-[300px] overflow-hidden relative">
          <!-- تصویر -->
          <img v-if="isImage" :src="previewUrl" class="max-h-[220px] rounded-lg object-contain shadow-sm" />

          <!-- ویدیو -->
          <video v-else-if="isVideo" :src="previewUrl" controls class="max-h-[220px] rounded-lg w-full"></video>

          <!-- سایر فایل‌ها -->
          <div v-else class="flex flex-col items-center gap-3 p-4">
            <div class="w-16 h-16 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            </div>
            <div class="text-center">
              <p class="text-xs font-bold text-gray-800 truncate max-w-[250px]">{{ file?.name }}</p>
              <p class="text-[11px] text-gray-400 mt-0.5">{{ formatSize(file?.size) }}</p>
            </div>
          </div>
        </div>

        <!-- پروگرس‌بار آپلود -->
        <div v-if="isUploading" class="px-5 py-4 bg-white border-t border-gray-100">
          <div class="flex items-center justify-between text-xs text-gray-600 mb-1.5 font-medium">
            <span>در حال آپلود فایل...</span>
            <span>{{ uploadProgress }}%</span>
          </div>
          <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
            <div class="bg-blue-600 h-full transition-all duration-200" :style="{ width: uploadProgress + '%' }"></div>
          </div>
        </div>

        <!-- بخش متنی و دکمه ارسال -->
        <div v-else class="p-4 bg-white border-t border-gray-100 flex flex-col gap-3">
          <input
            type="text"
            v-model="caption"
            placeholder="توضیحات (اختیاری)..."
            class="w-full bg-gray-50 text-xs text-gray-800 border border-gray-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all placeholder:text-gray-400"
            @keyup.enter="startUpload"
          />

          <div class="flex justify-end gap-2">
            <button @click="close" class="px-4 py-2 text-xs font-medium text-gray-600 hover:bg-gray-100 rounded-xl transition-all">
              انصراف
            </button>
            <button @click="startUpload" class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 active:scale-95 rounded-xl transition-all shadow-md shadow-blue-500/20">
              ارسال
            </button>
          </div>
        </div>
      </template>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onUnmounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  isOpen: Boolean,
  file: File,
  roomId: [Number, String],
  replyToId: [Number, String] // 🔴 پشتیبانی از ریپلای برای فایل
})

const emit = defineEmits(['close', 'uploaded'])

const caption = ref('')
const uploadProgress = ref(0)
const isUploading = ref(false)
const isSuccess = ref(false)
const previewUrl = ref(null)

const isImage = computed(() => props.file?.type?.startsWith('image/'))
const isVideo = computed(() => props.file?.type?.startsWith('video/'))

// پاک‌سازی حافظه ObjectURL
const clearPreviewUrl = () => {
  if (previewUrl.value) {
    URL.revokeObjectURL(previewUrl.value)
    previewUrl.value = null
  }
}

watch(() => props.file, (newFile) => {
  clearPreviewUrl()

  if (newFile) {
    caption.value = ''
    uploadProgress.value = 0
    isSuccess.value = false

    if (newFile.type?.startsWith('image/') || newFile.type?.startsWith('video/')) {
      previewUrl.value = URL.createObjectURL(newFile)
    }
  }
}, { immediate: true })

onUnmounted(() => {
  clearPreviewUrl()
})

const close = () => {
  if (isUploading.value) return
  isSuccess.value = false
  clearPreviewUrl()
  emit('close')
}

const formatSize = (bytes) => {
  if (!bytes) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i]
}

const startUpload = async () => {
  if (!props.file || isUploading.value) return

  isUploading.value = true
  uploadProgress.value = 0
  isSuccess.value = false

  const formData = new FormData()
  formData.append('file', props.file)

  if (caption.value.trim()) {
    formData.append('message', caption.value.trim())
  }

  if (props.replyToId) {
    formData.append('reply_to_id', props.replyToId)
  }

  try {
    if (window.Echo?.socketId()) {
      axios.defaults.headers.common["X-Socket-Id"] = window.Echo.socketId()
    }

    const res = await axios.post(`/chat/${props.roomId}/messages`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      },
      onUploadProgress: (progressEvent) => {
        if (progressEvent.total) {
          uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total)
        }
      }
    })

    const payload = res.data.message || res.data

    isSuccess.value = true

    // 🔴 ارسال داده استانداردشده به کامپوننت چت
    emit('uploaded', payload)

    setTimeout(() => {
      close()
    }, 800)

  } catch (err) {
    if (err.response && err.response.status === 422) {
      console.error('خطای اعتبارسنجی لاراول (422):', err.response.data.errors)
      alert('خطای اعتبارسنجی: ' + JSON.stringify(err.response.data.errors))
    } else {
      console.error('خطا در آپلود فایل:', err)
      alert('خطا در آپلود فایل. لطفاً مجدداً تلاش کنید.')
    }
  } finally {
    isUploading.value = false
  }
}
</script>
