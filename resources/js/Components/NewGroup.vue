<!-- دکمه شناور ایجاد گفتگو (FAB) -->
    <template>
    <button
      @click="openCreateModal"
      class="fixed bottom-6 left-6 w-14 h-14 bg-blue-600 hover:bg-blue-700 text-white rounded-full flex items-center justify-center shadow-lg active:scale-95 transition-all z-30"
      aria-label="گفتگوی جدید"
    >
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
      </svg>
    </button>

    <!-- مدال انتخاب نوع گفتگو / انتخاب مخاطبین -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
      <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-xl space-y-4 transition-all">

        <!-- گام اول: انتخاب نوع ساخت (گروه یا کانال) -->
        <template v-if="step === 1">
          <div class="flex items-center justify-between border-b pb-3">
            <h3 class="text-lg font-bold text-gray-900">ایجاد گفتگو جدید</h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="space-y-3 pt-2">
            <!-- گزینه گروه -->
            <button
              @click="selectType('group')"
              class="w-full flex items-center gap-4 p-3 rounded-xl hover:bg-gray-50 border border-gray-100 transition-all text-right group"
            >
              <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a5.97 5.97 0 00-.942 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                </svg>
              </div>
              <div>
                <h4 class="font-semibold text-gray-900">گروه جدید</h4>
                <p class="text-xs text-gray-500">گفتگوی چندنفره و تعاملی با اعضا</p>
              </div>
            </button>

            <!-- گزینه کانال -->
            <button
              @click="selectType('channel')"
              class="w-full flex items-center gap-4 p-3 rounded-xl hover:bg-gray-50 border border-gray-100 transition-all text-right group"
            >
              <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H5.25A2.25 2.25 0 013 13.5v-3a2.25 2.25 0 012.25-2.25h3c.704 0 1.402-.03 2.09-.09m0 7.68c.688.06 1.386.09 2.09.09h3c1.242 0 2.25-1.008 2.25-2.25v-3c0-1.242-1.008-2.25-2.25-2.25h-3c-.704 0-1.402.03-2.09.09m0 7.68a28.528 28.528 0 000-7.68" />
                </svg>
              </div>
              <div>
                <h4 class="font-semibold text-gray-900">کانال جدید</h4>
                <p class="text-xs text-gray-500">ارسال یک‌طرفه پیام و اخبار به دنبال‌کنندگان</p>
              </div>
            </button>
          </div>
        </template>

        <!-- گام دوم: مشخصات (نام) + انتخاب مخاطبین از لیست contacts -->
        <template v-else-if="step === 2">
          <div class="flex items-center justify-between border-b pb-3">
            <h3 class="text-lg font-bold text-gray-900">
              {{ createForm.type === 'group' ? 'ایجاد گروه جدید' : 'ایجاد کانال جدید' }}
            </h3>
            <button @click="step = 1" class="text-xs text-blue-600 hover:underline">بازگشت</button>
          </div>

          <form @submit.prevent="submitCreate" class="space-y-4 pt-2">
            <!-- نام گروه یا کانال -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">
                نام {{ createForm.type === 'group' ? 'گروه' : 'کانال' }}
              </label>
              <input
                v-model="createForm.name"
                type="text"
                required
                placeholder="مثلا: تیم فنی، اخبار شرکت..."
                class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <!-- انتخاب اعضا از لیست مخاطبین -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">
                افزودن اعضا از مخاطبین ({{ createForm.selectedMembers.length }} نفر انتخاب شده)
              </label>

              <div class="max-h-48 overflow-y-auto border border-gray-100 rounded-xl divide-y divide-gray-50">
                <div
                  v-for="contact in contacts"
                  :key="contact.id"
                  @click="toggleMember(contact.target_id)"
                  class="flex items-center justify-between p-2.5 hover:bg-gray-50 cursor-pointer transition-colors"
                >
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-gray-200 text-gray-600 text-xs font-bold flex items-center justify-center">
                      {{ (contact.custom_name || contact.target?.name || 'U').charAt(0) }}
                    </div>
                    <span class="text-sm text-gray-800 font-medium">
                      {{ contact.custom_name || contact.target?.name }}
                    </span>
                  </div>

                  <input
                    type="checkbox"
                    :checked="createForm.selectedMembers.includes(contact.target_id)"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 pointer-events-none"
                  />
                </div>

                <div v-if="contacts.length === 0" class="p-4 text-center text-xs text-gray-400">
                  هیچ مخاطبی یافت نشد.
                </div>
              </div>
            </div>

            <!-- دکمه ثبت -->
            <div class="flex items-center justify-end gap-2 pt-2">
              <button
                type="button"
                @click="closeModal"
                class="px-4 py-2 text-xs text-gray-600 hover:bg-gray-100 rounded-xl"
              >
                انصراف
              </button>
              <button
                type="submit"
                :disabled="loading"
                class="px-5 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 rounded-xl transition-all"
              >
                {{ loading ? 'در حال ایجاد...' : 'ایجاد گفتگو' }}
              </button>
            </div>
          </form>
        </template>

      </div>
    </div>
   </template>

<script setup>
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
  rooms: { type: Array, default: () => [] },
  contacts: { type: Array, default: () => [] } // دریافت مخاطبین از لاراول
})

// وضعیت‌های مدال
const showModal = ref(false)
const step = ref(1)
const loading = ref(false)

const createForm = ref({
  type: 'group', // یا 'channel'
  name: '',
  selectedMembers: []
})

const openCreateModal = () => {
  showModal.value = true
  step.value = 1
  createForm.value = { type: 'group', name: '', selectedMembers: [] }
}

const closeModal = () => {
  showModal.value = false
}

const selectType = (type) => {
  createForm.value.type = type
  step.value = 2
}

const toggleMember = (targetId) => {
  const index = createForm.value.selectedMembers.indexOf(targetId)
  if (index === -1) {
    createForm.value.selectedMembers.push(targetId)
  } else {
    createForm.value.selectedMembers.splice(index, 1)
  }
}

// ارسال فرم ایجاد گروه/کانال به بک‌اند
const submitCreate = () => {
  if (!createForm.value.name.trim()) return

  loading.value = true

  router.post('/rooms/create', createForm.value, {
    onSuccess: () => {
      closeModal()
      loading.value = false
    },
    onError: () => {
      loading.value = false
    }
  })
}
</script>
