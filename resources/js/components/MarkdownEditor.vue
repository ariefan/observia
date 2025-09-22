<template>
  <div class="space-y-3">
    <!-- Toolbar -->
    <div class="border rounded-t-md bg-gray-50 p-2 flex flex-wrap gap-1">
      <!-- Text formatting -->
      <div class="flex gap-1 border-r pr-2 mr-2">
        <button
          type="button"
          @click="insertFormatting('**', '**', 'Bold text')"
          class="px-2 py-1 text-sm bg-white border rounded hover:bg-gray-100"
          title="Bold"
        >
          <strong>B</strong>
        </button>
        <button
          type="button"
          @click="insertFormatting('*', '*', 'Italic text')"
          class="px-2 py-1 text-sm bg-white border rounded hover:bg-gray-100"
          title="Italic"
        >
          <em>I</em>
        </button>
      </div>

      <!-- Headers -->
      <div class="flex gap-1 border-r pr-2 mr-2">
        <button
          type="button"
          @click="insertFormatting('# ', '', 'Header 1')"
          class="px-2 py-1 text-sm bg-white border rounded hover:bg-gray-100"
          title="Header 1"
        >
          H1
        </button>
        <button
          type="button"
          @click="insertFormatting('## ', '', 'Header 2')"
          class="px-2 py-1 text-sm bg-white border rounded hover:bg-gray-100"
          title="Header 2"
        >
          H2
        </button>
        <button
          type="button"
          @click="insertFormatting('### ', '', 'Header 3')"
          class="px-2 py-1 text-sm bg-white border rounded hover:bg-gray-100"
          title="Header 3"
        >
          H3
        </button>
      </div>

      <!-- Lists -->
      <div class="flex gap-1 border-r pr-2 mr-2">
        <button
          type="button"
          @click="insertList('- ')"
          class="px-2 py-1 text-sm bg-white border rounded hover:bg-gray-100"
          title="Bullet List"
        >
          •
        </button>
        <button
          type="button"
          @click="insertList('1. ')"
          class="px-2 py-1 text-sm bg-white border rounded hover:bg-gray-100"
          title="Numbered List"
        >
          1.
        </button>
      </div>

      <!-- Link -->
      <div class="flex gap-1">
        <button
          type="button"
          @click="insertLink"
          class="px-2 py-1 text-sm bg-white border rounded hover:bg-gray-100"
          title="Link"
        >
          🔗
        </button>
      </div>
    </div>

    <!-- Editor -->
    <div class="relative">
      <textarea
        ref="textareaRef"
        :value="modelValue"
        @input="handleInput"
        @keydown="handleKeydown"
        :placeholder="placeholder"
        :rows="rows"
        class="w-full rounded-b-md border-t-0 border border-input bg-background px-3 py-2 text-base ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm font-mono"
      />
    </div>

    <!-- Preview Toggle -->
    <div class="flex justify-between items-center">
      <div class="text-xs text-gray-500">
        <details class="inline">
          <summary class="cursor-pointer hover:text-gray-700">Markdown Guide</summary>
          <div class="mt-2 space-y-1 text-xs">
            <div><strong>Bold:</strong> **text** atau __text__</div>
            <div><strong>Italic:</strong> *text* atau _text_</div>
            <div><strong>Headers:</strong> # H1, ## H2, ### H3</div>
            <div><strong>Lists:</strong> - item atau 1. item</div>
            <div><strong>Links:</strong> [text](url)</div>
          </div>
        </details>
      </div>
      <button
        type="button"
        @click="showPreview = !showPreview"
        class="px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded hover:bg-blue-200"
      >
        {{ showPreview ? 'Hide Preview' : 'Show Preview' }}
      </button>
    </div>

    <!-- Preview -->
    <div v-if="showPreview" class="border rounded-md p-4 bg-gray-50">
      <h4 class="text-sm font-medium mb-2 text-gray-700">Preview:</h4>
      <div class="prose prose-sm max-w-none" v-html="markdownPreview"></div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, nextTick } from 'vue'

const props = defineProps<{
  modelValue: string
  placeholder?: string
  rows?: number
}>()

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const textareaRef = ref<HTMLTextAreaElement>()
const showPreview = ref(false)

const handleInput = (event: Event) => {
  const target = event.target as HTMLTextAreaElement
  emit('update:modelValue', target.value)
}

const handleKeydown = (event: KeyboardEvent) => {
  // Tab key handling for indentation
  if (event.key === 'Tab') {
    event.preventDefault()
    insertAtCursor('  ')
  }
}

const insertAtCursor = (text: string) => {
  const textarea = textareaRef.value
  if (!textarea) return

  const start = textarea.selectionStart
  const end = textarea.selectionEnd
  const value = props.modelValue
  const newValue = value.substring(0, start) + text + value.substring(end)

  emit('update:modelValue', newValue)

  nextTick(() => {
    textarea.focus()
    textarea.setSelectionRange(start + text.length, start + text.length)
  })
}

const insertFormatting = (before: string, after: string, defaultText: string) => {
  const textarea = textareaRef.value
  if (!textarea) return

  const start = textarea.selectionStart
  const end = textarea.selectionEnd
  const value = props.modelValue
  const selectedText = value.substring(start, end)
  const textToWrap = selectedText || defaultText
  const newText = before + textToWrap + after

  const newValue = value.substring(0, start) + newText + value.substring(end)
  emit('update:modelValue', newValue)

  nextTick(() => {
    textarea.focus()
    if (!selectedText) {
      // If no text was selected, select the default text
      textarea.setSelectionRange(start + before.length, start + before.length + defaultText.length)
    } else {
      // If text was selected, place cursor after the inserted formatting
      textarea.setSelectionRange(start + newText.length, start + newText.length)
    }
  })
}

const insertList = (prefix: string) => {
  const textarea = textareaRef.value
  if (!textarea) return

  const start = textarea.selectionStart
  const value = props.modelValue

  // Find the start of the current line
  const beforeCursor = value.substring(0, start)
  const lineStart = beforeCursor.lastIndexOf('\n') + 1

  // Insert the list prefix at the beginning of the line
  const newValue = value.substring(0, lineStart) + prefix + value.substring(lineStart)
  emit('update:modelValue', newValue)

  nextTick(() => {
    textarea.focus()
    textarea.setSelectionRange(start + prefix.length, start + prefix.length)
  })
}

const insertLink = () => {
  const url = prompt('Enter URL:')
  if (url) {
    const text = prompt('Enter link text:') || 'Link'
    insertFormatting('[', `](${url})`, text)
  }
}

// Simple markdown to HTML converter for preview
const markdownPreview = computed(() => {
  let html = props.modelValue

  // Headers
  html = html.replace(/^### (.*$)/gim, '<h3 class="text-lg font-semibold mt-4 mb-2">$1</h3>')
  html = html.replace(/^## (.*$)/gim, '<h2 class="text-xl font-semibold mt-6 mb-3">$1</h2>')
  html = html.replace(/^# (.*$)/gim, '<h1 class="text-2xl font-bold mt-6 mb-4">$1</h1>')

  // Bold and italic
  html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
  html = html.replace(/\*(.*?)\*/g, '<em>$1</em>')

  // Links
  html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" class="text-blue-600 hover:underline">$1</a>')

  // Lists
  html = html.replace(/^\* (.*$)/gim, '<li class="ml-4">$1</li>')
  html = html.replace(/^- (.*$)/gim, '<li class="ml-4">$1</li>')
  html = html.replace(/^\d+\. (.*$)/gim, '<li class="ml-4">$1</li>')

  // Line breaks
  html = html.replace(/\n\n/g, '</p><p class="mb-3">')
  html = html.replace(/\n/g, '<br>')

  // Wrap in paragraphs if not starting with a tag
  if (!html.startsWith('<')) {
    html = '<p class="mb-3">' + html + '</p>'
  }

  // Wrap lists in ul tags
  html = html.replace(/(<li.*?>.*?<\/li>)/gs, (match) => {
    if (!match.includes('<ul>')) {
      return '<ul class="list-disc list-inside mb-3">' + match + '</ul>'
    }
    return match
  })

  return html
})
</script>