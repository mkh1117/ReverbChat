<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4"
    dir="rtl"
    @click.self="close"
  >
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-xs p-5 space-y-4 animate-fade-in text-right">
      <h3 class="text-sm font-bold text-gray-800">حذف پیام</h3>
      <p class="text-xs text-gray-500 leading-relaxed">
        نحوه حذف پیام را انتخاب کنید:
      </p>

      <div class="flex flex-col gap-2 pt-1">
        <button
          @click="confirm('everyone')"
          class="w-full py-2.5 px-3 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded-xl transition-all shadow-sm active:scale-95"
        >
          حذف برای همه
        </button>
        <button
          @click="confirm('me')"
          class="w-full py-2.5 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-medium rounded-xl transition-all active:scale-95"
        >
          حذف فقط برای من
        </button>
        <button
          @click="close"
          class="w-full py-2 px-3 text-gray-400 hover:text-gray-600 text-xs font-medium rounded-xl transition-all"
        >
          انصراف
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  isOpen: Boolean
})

const emit = defineEmits(['close', 'confirm'])

const close = () => {
  emit('close')
}

const confirm = (type) => {
  emit('confirm', type)
}
</script>
