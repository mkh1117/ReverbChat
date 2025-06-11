<template>
<div class="bg-stone-300 h-screen flex flex-col">
  <header class="bg-blue-600 text-white p-4 text-lg font-bold">
    {{ user.name }}
  </header>

  <!-- Chat Messages -->
  <div id="chat-box" class="flex-1 overflow-y-auto p-4 space-y-4 ">
    <div>
    </div>
    <!-- Other user's message -->
    <!-- <div class="flex items-start flex-col">
      <div class="bg-white p-3  rounded-xl shadow max-w-xs mt-4" v-for="m in massages">
        {{ m.message }}
      </div>
    </div> -->

    <!-- Current user's message -->
    <div :class="['flex' ,'items-start' ,'space-x-2',{'justify-end':m.user=='sender'} ]"  v-for="m in massages " >
      <div  :class="[ 'p-3', 'rounded-xl' ,'shadow' ,'max-w-xs' ,{'bg-blue-500 text-white':m.user=='sender','bg-white':m.user=='resever'}]">
        {{ m.message }}
      </div>
    </div>

    <!-- More messages can be appended dynamically here -->

  </div>

  <!-- Message Input -->
  <div class="p-4 bg-white border-t flex items-center space-x-2">
    <input type="text" v-model="message" placeholder="پیام خود را بنویسید..." class="flex-1 border rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" >
    <button @click="send" class="bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700">
      ارسال
    </button>
  </div>
</div>
</template>

<script setup>
defineProps({
    name:String,
    id:Number,
    room_id:Number,
    user:Object
})
</script>
<script>
import axios from 'axios';
import '../echo';
export default {
    name: "App",
    data() {
        return {
            messager:'',
            massages:[],
            test2:''
        }
    },
    methods: {
        send(){
            axios.defaults.headers.common["X-Socket-Id"]= Echo.socketId();
            axios.post('/testreverb',{message:this.message,room_id:this.room_id})
                .catch(error => {
                    console.error("خطا در دریافت داده‌ها:", error);
                });
            this.massages.push({message:this.message,user:'sender'});
            this.message=''
        }
    },
    mounted() {
        Echo.private('test.'+this.room_id).listen('TestEvent', (e) => {
        this.massages.push({message:e.message,user:'resever'});
    });
    },
}
</script>

<style scoped>
h1 {
    color: green;
}
</style>
