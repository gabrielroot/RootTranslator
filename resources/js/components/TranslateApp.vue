<template>
  <div class="translate-app bg-white rounded-md shadow-md p-6 max-w-xl mx-auto mt-10">
    <h1 class="text-2xl font-bold mb-4 text-center">Tradutor (LibreTranslate)</h1>
    <div class="flex flex-col md:flex-row gap-4 mb-4">
      <div class="flex-1">
        <label class="block mb-1 font-medium">De</label>
        <select v-model="from" class="w-full border rounded p-2 mb-2">
          <option v-for="lang in languages" :key="lang.code" :value="lang.code">{{ lang.name }}</option>
        </select>
        <textarea v-model="text" class="w-full border rounded p-2 h-32" placeholder="Digite o texto..."></textarea>
      </div>
      <div class="flex-1">
        <label class="block mb-1 font-medium">Para</label>
        <select v-model="to" class="w-full border rounded p-2 mb-2">
          <option v-for="lang in languages" :key="lang.code" :value="lang.code">{{ lang.name }}</option>
        </select>
        <textarea v-model="translated" class="w-full border rounded p-2 h-32 bg-gray-100" readonly></textarea>
      </div>
    </div>
    <div class="flex justify-center">
      <button @click="translate" :disabled="loading || !text.trim()" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 disabled:opacity-50">
        {{ loading ? 'Traduzindo...' : 'Traduzir' }}
      </button>
    </div>
    <div v-if="error" class="text-red-600 text-center mt-2">{{ error }}</div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const text = ref('')
const translated = ref('')
const from = ref('pt')
const to = ref('en')
const loading = ref(false)
const error = ref('')
const languages = []

loadLanguages()

async function loadLanguages() {
  loading.value = true
  error.value = ''
  try {
    const res = await axios.get('/api/languages')
    languages.push(...res.data.map(lang => ({ code: lang.code, name: lang.name })))
  } catch (e) {
    error.value = 'Erro ao carregar idiomas. Tente novamente.'
  } finally {
    loading.value = false 
  }
}

async function translate() {
  loading.value = true
  error.value = ''
  translated.value = ''
  try {
    const res = await axios.post('/api/translate', {
      q: text.value,
      source: from.value,
      target: to.value,
      format: 'text',
    }, {
      headers: { 'accept': 'application/json' }
    })
    translated.value = res.data.translatedText
  } catch (e) {
    error.value = 'Erro ao traduzir. Tente novamente.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.translate-app {
  box-shadow: 0 2px 16px 0 rgba(0,0,0,0.08);
}
</style>
