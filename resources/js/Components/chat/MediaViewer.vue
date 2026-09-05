<template>
  <div class="mb-2">
    <!-- ۱. نمایش عکس -->
    <div v-if="fileType?.startsWith('image/')" class="relative group">
      <img
        :src="fileUrl"
        @click="showModal = true"
        class="h-48 w-full object-cover rounded-xl shadow-sm cursor-pointer hover:opacity-90 transition-all border border-black/5"
        alt="Image Attachment"
      />

      <Teleport to="body">
        <div
          v-if="showModal"
          class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 transition-all"
          @click.self="showModal = false"
        >
          <div class="absolute top-4 right-4 flex items-center gap-3 z-10">
            <a
              :href="downloadUrl"
              :download="fileName"
              target="_self"
              rel="external"
              class="flex items-center gap-1.5 px-3 py-2 bg-white/10 hover:bg-white/20 text-white text-xs rounded-lg transition-colors backdrop-blur-md"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
              دانلود
            </a>

            <button
              @click="showModal = false"
              class="p-2 bg-white/10 hover:bg-white/20 text-white rounded-lg transition-colors backdrop-blur-md"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>

          <img
            :src="fileUrl"
            class="max-w-full max-h-[85vh] object-contain rounded-lg shadow-2xl select-none"
          />
        </div>
      </Teleport>
    </div>

    <!-- ۲. نمایش ویدیو -->
    <video
      v-else-if="fileType?.startsWith('video/')"
      controls
      preload="metadata"
      class="h-52 w-full object-cover rounded-xl shadow-sm"
      :src="fileUrl"
    ></video>

    <!-- ۳. نمایش فایل‌های عمومی (Zip, PDF و ...) -->
    <a
      v-else
      :href="downloadUrl"
      :download="fileName"
      target="_self"
      rel="external"
      class="flex items-center gap-3 p-2.5 rounded-xl bg-black/5 hover:bg-black/10 transition-colors border border-black/5 cursor-pointer"
    >
      <div class="w-10 h-10 rounded-lg bg-blue-500 text-white flex items-center justify-center flex-shrink-0">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
      </div>
      <div class="flex flex-col min-w-0 flex-1 text-right">
        <span class="text-xs font-bold truncate">{{ fileName || 'دانلود فایل' }}</span>
        <span class="text-[10px] text-gray-500">برای دانلود کلیک کنید</span>
      </div>
    </a>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  message: {
    type: Object,
    required: true
  }
});

const showModal = ref(false);

const fileType = computed(() => props.message.file_type || props.message.mime_type);


const fileName = computed(() => props.message.original_name || props.message.file_name || 'download');


const fileUrl = computed(() => {
  const attachmentId = props.message.attachment_id;
  if (attachmentId) {
    return route('attachments.stream', attachmentId);
  }
  return `/chat/files/${props.message.id}`;
});

const downloadUrl = computed(() => {
  const attachmentId = props.message.attachment_id;
  if (attachmentId) {
    return route('attachments.stream', attachmentId) + '?download=1';
  }
  return `/chat/files/${props.message.id}?download=1`;
});
</script>
