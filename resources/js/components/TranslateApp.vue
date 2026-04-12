<template>
  <div>
    <div :class="['app-body-bg', { dark: isDark }]" />
    <div class="app-wrapper" :class="{ 'dark': isDark }">
      <div class="main-layout">
      <div class="translate-app">
      <!-- Header com toggle de tema -->
      <div class="header">
        <div class="logo-section">
          <div class="logo-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12.87 15.07l-2.54-2.51.03-.03A17.52 17.52 0 0014.07 6H17V4h-7V2H8v2H1v2h11.17C11.5 7.92 10.44 9.75 9 11.35 8.07 10.32 7.3 9.19 6.69 8h-2c.73 1.63 1.73 3.17 2.98 4.56l-5.09 5.02L4 19l5-5 3.11 3.11.76-2.04zM18.5 10h-2L12 22h2l1.12-3h4.75L21 22h2l-4.5-12zm-2.62 7l1.62-4.33L19.12 17h-3.24z"/>
            </svg>
          </div>
          <h1 class="title">Tradutor</h1>
        </div>
        <button @click="toggleTheme" class="theme-toggle" :title="isDark ? 'Modo claro' : 'Modo escuro'">
          <transition name="rotate" mode="out-in">
            <svg v-if="isDark" key="sun" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 7c-2.76 0-5 2.24-5 5s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5zM2 13h2c.55 0 1-.45 1-1s-.45-1-1-1H2c-.55 0-1 .45-1 1s.45 1 1 1zm18 0h2c.55 0 1-.45 1-1s-.45-1-1-1h-2c-.55 0-1 .45-1 1s.45 1 1 1zM11 2v2c0 .55.45 1 1 1s1-.45 1-1V2c0-.55-.45-1-1-1s-1 .45-1 1zm0 18v2c0 .55.45 1 1 1s1-.45 1-1v-2c0-.55-.45-1-1-1s-1 .45-1 1zM5.99 4.58a.996.996 0 00-1.41 0 .996.996 0 000 1.41l1.06 1.06c.39.39 1.03.39 1.41 0s.39-1.03 0-1.41L5.99 4.58zm12.37 12.37a.996.996 0 00-1.41 0 .996.996 0 000 1.41l1.06 1.06c.39.39 1.03.39 1.41 0a.996.996 0 000-1.41l-1.06-1.06zm1.06-10.96a.996.996 0 000-1.41.996.996 0 00-1.41 0l-1.06 1.06c-.39.39-.39 1.03 0 1.41s1.03.39 1.41 0l1.06-1.06zM7.05 18.36a.996.996 0 000-1.41.996.996 0 00-1.41 0l-1.06 1.06c-.39.39-.39 1.03 0 1.41s1.03.39 1.41 0l1.06-1.06z"/>
            </svg>
            <svg v-else key="moon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 3a9 9 0 109 9c0-.46-.04-.92-.1-1.36a5.389 5.389 0 01-4.4 2.26 5.403 5.403 0 01-3.14-9.8c-.44-.06-.9-.1-1.36-.1z"/>
            </svg>
          </transition>
        </button>
      </div>

      <!-- Loading overlay inicial -->
      <transition name="fade">
        <div v-if="loadingLanguages" class="loading-overlay">
          <div class="spinner"></div>
          <span>Carregando idiomas...</span>
        </div>
      </transition>

      <!-- Conteúdo principal -->
      <div class="content" :class="{ 'loading': loadingLanguages }">
        <div class="translation-grid">
          <!-- Input Section -->
          <div class="translation-card input-card">
            <div class="card-header">
              <label class="label">De</label>
              <select v-model="from" class="lang-select">
                <option v-for="lang in [{ code: 'auto', name: '(Detectar idioma)' }, ...languages]" :key="lang.code" :value="lang.code">{{ lang.name }}</option>
              </select>
              <span v-if="from === 'auto' && detectedLanguageCode" class="detected-lang">
                {{ getLanguageName(detectedLanguageCode) }}
              </span>
            </div>
            <div class="textarea-wrapper">
              <textarea 
                v-model="text" 
                class="textarea"
                placeholder="Digite o texto para traduzir..."
                @input="onInput"
                :maxlength="500"
              ></textarea>
              <span v-if="text" class="char-count">{{ text.length }}/500 caracteres</span>
            </div>
          </div>

          <!-- Swap Button -->
          <button @click="swapLanguages" class="swap-btn" title="Trocar idiomas">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
              <path d="M6.99 11L3 15l3.99 4v-3H14v-2H6.99v-3zM21 9l-3.99-4v3H10v2h7.01v3L21 9z"/>
            </svg>
          </button>

          <!-- Output Section -->
          <div class="translation-card output-card">
            <div class="card-header">
              <label class="label">Para</label>
              <select v-model="to" class="lang-select">
                <option v-for="lang in languages" :key="lang.code" :value="lang.code">{{ lang.name }}</option>
              </select>
            </div>
            <div class="textarea-wrapper">
              <textarea 
                :value="getDisplayedTranslation()" 
                class="textarea output-textarea" 
                placeholder="A tradução aparecerá aqui..."
                readonly
              ></textarea>
              
              <!-- Alternative circles -->
              <transition name="fade">
                <div v-if="alternatives.length > 0" class="alternatives-container">
                  <button 
                    v-for="(alt, index) in 3" 
                    :key="index"
                    @click="selectAlternative(index)"
                    class="alternative-circle"
                    :class="{ 
                      'active': currentAlternativeIndex === index,
                      'disabled': index >= alternatives.length 
                    }"
                    :disabled="index >= alternatives.length"
                    :title="index < alternatives.length ? alternatives[index] : 'Sem alternativa'"
                  >
                    {{ index + 1 }}
                  </button>
                </div>
              </transition>
              
              <transition name="fade">
                <button v-if="translated" @click="copyToClipboard" class="copy-btn" title="Copiar">
                  <svg v-if="!copied" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/>
                  </svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                  </svg>
                </button>
              </transition>
              <transition name="slide-up">
                <div v-if="loading" class="translating-indicator">
                  <div class="pulse-dot"></div>
                  <div class="pulse-dot"></div>
                  <div class="pulse-dot"></div>
                </div>
              </transition>
            </div>
          </div>
        </div>

        <!-- Action Button -->
        <div class="action-section">
          <button 
            @click="translate" 
            :disabled="loading || !text.trim()" 
            class="translate-btn"
            :class="{ 'translating': loading }"
          >
            <span class="btn-content">
              <svg v-if="!loading" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12.87 15.07l-2.54-2.51.03-.03A17.52 17.52 0 0014.07 6H17V4h-7V2H8v2H1v2h11.17C11.5 7.92 10.44 9.75 9 11.35 8.07 10.32 7.3 9.19 6.69 8h-2c.73 1.63 1.73 3.17 2.98 4.56l-5.09 5.02L4 19l5-5 3.11 3.11.76-2.04zM18.5 10h-2L12 22h2l1.12-3h4.75L21 22h2l-4.5-12zm-2.62 7l1.62-4.33L19.12 17h-3.24z"/>
              </svg>
              <div v-else class="btn-spinner"></div>
              <span>
                <span v-if="isDetecting">Detectando...</span>
                <span v-else-if="loading">Traduzindo...</span>
                <span v-else-if="from === 'auto'">Detectar</span>
                <span v-else>Traduzir</span>
              </span>
            </span>
          </button>
        </div>

        <!-- Error Message -->
        <transition name="shake">
          <div v-if="error" class="error-message">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
            </svg>
            {{ error }}
          </div>
        </transition>
      </div>

      <!-- Footer -->
      <div class="footer">
        <span>Powered by LibreTranslate</span>
      </div>
    </div>

      <!-- History Aside -->
      <aside class="history-aside" :class="{ 'expanded': historyExpanded }">
        <!-- Toggle Button (sempre visível) -->
        <button @click="historyExpanded = !historyExpanded" class="history-toggle-btn" :title="historyExpanded ? 'Recolher histórico' : 'Expandir histórico'">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="22" height="22">
            <path d="M13 3a9 9 0 0 0-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42A8.954 8.954 0 0 0 13 21a9 9 0 0 0 0-18zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/>
          </svg>
          <span v-if="history.length > 0" class="history-badge">{{ history.length }}</span>
        </button>
        
        <!-- Conteúdo do aside (só visível quando expandido) -->
        <transition name="slide-history">
          <div v-show="historyExpanded" class="history-content">
            <div class="history-header">
              <h2 class="history-title">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                  <path d="M13 3a9 9 0 0 0-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42A8.954 8.954 0 0 0 13 21a9 9 0 0 0 0-18zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/>
                </svg>
                Histórico
              </h2>
              <button v-if="history.length > 0" @click="clearHistory" class="clear-history-btn" title="Limpar histórico">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                  <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                </svg>
              </button>
            </div>
            <div class="history-list">
              <div v-if="history.length === 0" class="history-empty">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="48" height="48">
                  <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
                <p>Nenhuma tradução ainda</p>
              </div>
              <div 
                v-for="(item, index) in history" 
                :key="index" 
                class="history-item"
                @click="loadFromHistory(item)"
              >
                <div class="history-item-langs">
                  <span class="history-lang">{{ getLanguageName(item.from) || item.from }}</span>
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="14" height="14">
                    <path d="M12 4l-1.41 1.41L16.17 11H4v2h12.17l-5.58 5.59L12 20l8-8z"/>
                  </svg>
                  <span class="history-lang">{{ getLanguageName(item.to) || item.to }}</span>
                </div>
                <p class="history-item-text">{{ item.text }}</p>
                <p class="history-item-translated">{{ item.translated }}</p>
                <span class="history-item-date">{{ formatDate(item.date) }}</span>
              </div>
            </div>
          </div>
        </transition>
      </aside>
      </div>
    </div>
  </div>
</template>

<script setup>
import '../../css/main.css'
import '../../css/translateapp.css'
import { ref, onMounted, watch } from 'vue'
let debounceTimeout = null
let translateAbortController = null
import axios from 'axios'

const text = ref('')
const translated = ref('')
const from = ref('auto')
const to = ref('en')
const loading = ref(false)
const loadingLanguages = ref(true)
const error = ref('')
const languages = ref([])
const isDark = ref(false)
const copied = ref(false)
const alternatives = ref([])
const currentAlternativeIndex = ref(-1) // -1 = texto principal
const detectedLanguageCode = ref('') // Código do idioma detectado
const isDetecting = ref(false) // True se está detectando
const history = ref([]) // Histórico de traduções
const MAX_HISTORY = 50 // Limite máximo de traduções no histórico
const historyExpanded = ref(false) // Controla se o aside está expandido


onMounted(() => {
  // Carregar tema salvo
  const savedTheme = localStorage.getItem('translator-theme')
  isDark.value = savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)
  // Carregar histórico do localStorage
  loadHistory()
  loadLanguages()
  // Tradução automática ao abrir se já houver texto
  if (text.value.trim()) triggerAutoTranslate()
})

function onInput(e) {
  // Limite de 500 caracteres (garantia extra)
  if (e.target.value.length > 500) {
    text.value = e.target.value.slice(0, 500)
  }
  autoResizeAll()
  triggerAutoTranslate()
}

function autoResizeAll() {
  setTimeout(() => {
    document.querySelectorAll('.textarea').forEach(el => {
      el.style.height = 'auto'
      el.style.height = (el.scrollHeight + 10) + 'px'
    })
  }, 0)
}

onMounted(() => {
  autoResizeAll()
})

watch([text, translated], () => {
  autoResizeAll()
})

function triggerAutoTranslate() {
  if (debounceTimeout) clearTimeout(debounceTimeout)
  if (!text.value.trim()) {
    translated.value = ''
    return
  }
  debounceTimeout = setTimeout(() => {
    translate()
  }, 500)
}

function toggleTheme() {
  isDark.value = !isDark.value
  localStorage.setItem('translator-theme', isDark.value ? 'dark' : 'light')
}

function swapLanguages() {
  if (from.value === 'auto') {
    // Se "De" for auto, não permite swap
    error.value = 'Não é possível trocar idiomas quando "De" está definido como detectar automaticamente.'
    return
  }

  const tempFrom = from.value
  const tempText = text.value
  from.value = to.value
  to.value = tempFrom
  text.value = getDisplayedTranslation()
  translated.value = tempText
  alternatives.value = []
  currentAlternativeIndex.value = -1
  detectedLanguageCode.value = ''
}

async function copyToClipboard() {
  try {
    await navigator.clipboard.writeText(getDisplayedTranslation())
    copied.value = true
    setTimeout(() => copied.value = false, 2000)
  } catch (e) {
    console.error('Failed to copy:', e)
  }
}

async function loadLanguages() {
  loadingLanguages.value = true
  error.value = ''
  try {
    const res = await axios.get('/api/languages')
    languages.value = res.data.map(lang => ({ code: lang.code, name: lang.name }))
  } catch (e) {
    error.value = 'Erro ao carregar idiomas. Tente novamente.'
  } finally {
    loadingLanguages.value = false 
  }
}

function getLanguageName(code) {
  if (!code) return ''
  const lang = languages.value.find(l => l.code.toLowerCase() === code.toLowerCase())
  return lang ? lang.name : ''
}

async function detectLanguage() {
  // Cancela requisição anterior
  if (translateAbortController) {
    translateAbortController.abort()
  }

  translateAbortController = new AbortController()
  loading.value = true
  isDetecting.value = true
  error.value = ''
  translated.value = ''
  alternatives.value = []
  currentAlternativeIndex.value = -1

  try {
    const res = await axios.post('/api/detect', {
      q: text.value,
    }, {
      headers: { 'accept': 'application/json' },
      signal: translateAbortController.signal
    })
    
    // Armazenar o código do idioma detectado
    detectedLanguageCode.value = res.data
    isDetecting.value = false
    translate({ autoDetected: res.data });

  } catch (e) {
    if (axios.isCancel?.(e) || e?.code === 'ERR_CANCELED' || e?.name === 'CanceledError' || e?.message === 'canceled') {
      // Cancelado, não mostra erro
    } else {
      error.value = 'Erro ao detectar idioma. Tente novamente.'
    }
    isDetecting.value = false
  }
}

async function translate({ autoDetected = null } = {}) {
  if (from.value === 'auto' && !autoDetected) {
    await detectLanguage()
    return
  }

  if (!text.value.trim()) {
    translated.value = ''
    alternatives.value = []
    currentAlternativeIndex.value = -1
    detectedLanguageCode.value = ''
    return
  }
  // Cancela requisição anterior
  if (translateAbortController) {
    translateAbortController.abort()
  }
  translateAbortController = new AbortController()
  loading.value = true
  error.value = ''
  translated.value = ''
  alternatives.value = []
  currentAlternativeIndex.value = -1
  try {
    const res = await axios.post('/api/translate', {
      q: text.value,
      source: autoDetected || from.value,
      target: to.value,
      format: 'text',
    }, {
      headers: { 'accept': 'application/json' },
      signal: translateAbortController.signal
    })
    translated.value = res.data.translatedText
    // Armazena alternativas (máximo 3)
    alternatives.value = (res.data.alternatives || []).slice(0, 3)
    // Salvar no histórico
    saveToHistory({
      text: text.value,
      translated: res.data.translatedText,
      from: autoDetected || from.value,
      to: to.value,
      date: new Date().toISOString()
    })
  } catch (e) {
    if (axios.isCancel?.(e) || e?.code === 'ERR_CANCELED' || e?.name === 'CanceledError' || e?.message === 'canceled') {
      // Cancelado, não mostra erro
    } else {
      error.value = e?.response?.data?.message || 'Erro ao traduzir. Tente novamente.'
    }
  } finally {
    loading.value = false
  }
}

function selectAlternative(index) {
  if (index === currentAlternativeIndex.value) {
    // Se clicar no mesmo, volta para o texto principal
    currentAlternativeIndex.value = -1
    return
  }
  if (index >= 0 && index < alternatives.value.length) {
    currentAlternativeIndex.value = index
  }
}

function getDisplayedTranslation() {
  if (currentAlternativeIndex.value >= 0 && currentAlternativeIndex.value < alternatives.value.length) {
    return alternatives.value[currentAlternativeIndex.value]
  }
  return translated.value
}

// ===== Funções de Histórico =====
function loadHistory() {
  try {
    const saved = localStorage.getItem('translator-history')
    if (saved) {
      history.value = JSON.parse(saved)
    }
  } catch (e) {
    console.error('Erro ao carregar histórico:', e)
    history.value = []
  }
}

function saveToHistory(item) {
  // Evitar duplicatas consecutivas
  if (history.value.length > 0) {
    const last = history.value[0]
    if (last.text === item.text && last.from === item.from && last.to === item.to) {
      return
    }
  }
  
  // Adicionar no início
  history.value.unshift(item)
  
  // Manter apenas MAX_HISTORY itens
  if (history.value.length > MAX_HISTORY) {
    history.value = history.value.slice(0, MAX_HISTORY)
  }
  
  // Salvar no localStorage
  try {
    localStorage.setItem('translator-history', JSON.stringify(history.value))
  } catch (e) {
    console.error('Erro ao salvar histórico:', e)
  }
}

function clearHistory() {
  history.value = []
  localStorage.removeItem('translator-history')
}

function loadFromHistory(item) {
  text.value = item.text
  from.value = item.from
  to.value = item.to
  translated.value = item.translated
  alternatives.value = []
  currentAlternativeIndex.value = -1
  detectedLanguageCode.value = ''
}

function formatDate(dateStr) {
  const date = new Date(dateStr)
  const now = new Date()
  const diff = now - date
  
  // Menos de 1 minuto
  if (diff < 60000) return 'Agora'
  // Menos de 1 hora
  if (diff < 3600000) return `${Math.floor(diff / 60000)} min atrás`
  // Menos de 24 horas
  if (diff < 86400000) return `${Math.floor(diff / 3600000)}h atrás`
  // Menos de 7 dias
  if (diff < 604800000) return `${Math.floor(diff / 86400000)}d atrás`
  
  return date.toLocaleDateString('pt-BR')
}

// Tradução automática ao trocar idioma
watch([from, to], () => {
  if (text.value.trim()) triggerAutoTranslate()
})
</script>


