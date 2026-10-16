<script setup>
import { Placeholder } from '@tiptap/extension-placeholder'
import { TextAlign } from '@tiptap/extension-text-align'
import { Underline } from '@tiptap/extension-underline'
import { StarterKit } from '@tiptap/starter-kit'
import {
  EditorContent,
  useEditor,
} from '@tiptap/vue-3'

const props = defineProps({
  modelValue: {
    type: String,
    required: true,
  },
  placeholder: {
    type: String,
    required: false,
  },
})

const emit = defineEmits(['update:modelValue'])

const editorRef = ref()

const editor = useEditor({
  content: props.modelValue,
  extensions: [
    StarterKit,
    TextAlign.configure({
      types: [
        'heading',
        'paragraph',
      ],
    }),
    Placeholder.configure({ placeholder: props.placeholder ?? 'Write something here...' }),
    Underline,
  ],
  onUpdate() {
    if (!editor.value)
      return
    emit('update:modelValue', editor.value.getHTML())
  },
})

watch(() => props.modelValue, () => {
  const isSame = editor.value?.getHTML() === props.modelValue
  if (isSame)
    return
  editor.value?.commands.setContent(props.modelValue)
})
</script>

<template>
  <div class="tiptap-editor-container">
    <div
      v-if="editor"
      class="editor-toolbar d-flex ga-1 pa-1 px-2 flex-wrap align-center border-b"
    >
      <!-- History Group -->
      <IconBtn
        size="x-small"
        rounded="sm"
        variant="text"
        :disabled="!editor.can().undo()"
        title="Undo (Ctrl+Z)"
        @click="editor.chain().focus().undo().run()"
      >
        <VIcon icon="mdi-undo" size="16" />
      </IconBtn>
      <IconBtn
        size="x-small"
        rounded="sm"
        variant="text"
        :disabled="!editor.can().redo()"
        title="Redo (Ctrl+Y)"
        @click="editor.chain().focus().redo().run()"
      >
        <VIcon icon="mdi-redo" size="16" />
      </IconBtn>

      <VDivider vertical class="my-1 mx-1" style="height: 16px;" />

      <!-- Headings Group -->
      <IconBtn
        size="x-small"
        rounded="sm"
        :variant="editor.isActive('paragraph') ? 'tonal' : 'text'"
        :color="editor.isActive('paragraph') ? 'primary' : 'default'"
        title="Paragraf Biasa"
        @click="editor.chain().focus().setParagraph().run()"
      >
        <VIcon icon="mdi-format-paragraph" size="16" />
      </IconBtn>
      <IconBtn
        size="x-small"
        rounded="sm"
        :variant="editor.isActive('heading', { level: 2 }) ? 'tonal' : 'text'"
        :color="editor.isActive('heading', { level: 2 }) ? 'primary' : 'default'"
        title="Heading 2 (Judul Bagian)"
        @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
      >
        <VIcon icon="mdi-format-header-2" size="16" />
      </IconBtn>
      <IconBtn
        size="x-small"
        rounded="sm"
        :variant="editor.isActive('heading', { level: 3 }) ? 'tonal' : 'text'"
        :color="editor.isActive('heading', { level: 3 }) ? 'primary' : 'default'"
        title="Heading 3 (Sub-judul)"
        @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
      >
        <VIcon icon="mdi-format-header-3" size="16" />
      </IconBtn>

      <VDivider vertical class="my-1 mx-1" style="height: 16px;" />

      <!-- Formatting Group -->
      <IconBtn
        size="x-small"
        rounded="sm"
        :variant="editor.isActive('bold') ? 'tonal' : 'text'"
        :color="editor.isActive('bold') ? 'primary' : 'default'"
        title="Bold (Tebal)"
        @click="editor.chain().focus().toggleBold().run()"
      >
        <VIcon icon="mdi-format-bold" size="16" />
      </IconBtn>
      <IconBtn
        size="x-small"
        rounded="sm"
        :variant="editor.isActive('italic') ? 'tonal' : 'text'"
        :color="editor.isActive('italic') ? 'primary' : 'default'"
        title="Italic (Miring)"
        @click="editor.chain().focus().toggleItalic().run()"
      >
        <VIcon icon="mdi-format-italic" size="16" />
      </IconBtn>
      <IconBtn
        size="x-small"
        rounded="sm"
        :variant="editor.isActive('underline') ? 'tonal' : 'text'"
        :color="editor.isActive('underline') ? 'primary' : 'default'"
        title="Underline (Garis Bawah)"
        @click="editor.commands.toggleUnderline()"
      >
        <VIcon icon="mdi-format-underline" size="16" />
      </IconBtn>
      <IconBtn
        size="x-small"
        rounded="sm"
        :variant="editor.isActive('strike') ? 'tonal' : 'text'"
        :color="editor.isActive('strike') ? 'primary' : 'default'"
        title="Strikethrough (Coret)"
        @click="editor.chain().focus().toggleStrike().run()"
      >
        <VIcon icon="mdi-format-strikethrough-variant" size="16" />
      </IconBtn>

      <VDivider vertical class="my-1 mx-1" style="height: 16px;" />

      <!-- Lists & Quotes Group -->
      <IconBtn
        size="x-small"
        rounded="sm"
        :variant="editor.isActive('bulletList') ? 'tonal' : 'text'"
        :color="editor.isActive('bulletList') ? 'primary' : 'default'"
        title="Daftar Butir (Bullet List)"
        @click="editor.chain().focus().toggleBulletList().run()"
      >
        <VIcon icon="mdi-format-list-bulleted" size="16" />
      </IconBtn>
      <IconBtn
        size="x-small"
        rounded="sm"
        :variant="editor.isActive('orderedList') ? 'tonal' : 'text'"
        :color="editor.isActive('orderedList') ? 'primary' : 'default'"
        title="Daftar Nomor (Numbered List)"
        @click="editor.chain().focus().toggleOrderedList().run()"
      >
        <VIcon icon="mdi-format-list-numbered" size="16" />
      </IconBtn>
      <IconBtn
        size="x-small"
        rounded="sm"
        :variant="editor.isActive('blockquote') ? 'tonal' : 'text'"
        :color="editor.isActive('blockquote') ? 'primary' : 'default'"
        title="Kutipan (Blockquote)"
        @click="editor.chain().focus().toggleBlockquote().run()"
      >
        <VIcon icon="mdi-format-quote-close" size="16" />
      </IconBtn>
      <IconBtn
        size="x-small"
        rounded="sm"
        :variant="editor.isActive('codeBlock') ? 'tonal' : 'text'"
        :color="editor.isActive('codeBlock') ? 'primary' : 'default'"
        title="Blok Kode Teknis"
        @click="editor.chain().focus().toggleCodeBlock().run()"
      >
        <VIcon icon="mdi-code-braces" size="16" />
      </IconBtn>

      <VDivider vertical class="my-1 mx-1" style="height: 16px;" />

      <!-- Alignments Group -->
      <IconBtn
        size="x-small"
        rounded="sm"
        :variant="editor.isActive({ textAlign: 'left' }) ? 'tonal' : 'text'"
        :color="editor.isActive({ textAlign: 'left' }) ? 'primary' : 'default'"
        title="Rata Kiri"
        @click="editor.chain().focus().setTextAlign('left').run()"
      >
        <VIcon icon="mdi-format-align-left" size="16" />
      </IconBtn>
      <IconBtn
        size="x-small"
        rounded="sm"
        :variant="editor.isActive({ textAlign: 'center' }) ? 'tonal' : 'text'"
        :color="editor.isActive({ textAlign: 'center' }) ? 'primary' : 'default'"
        title="Rata Tengah"
        @click="editor.chain().focus().setTextAlign('center').run()"
      >
        <VIcon icon="mdi-format-align-center" size="16" />
      </IconBtn>
      <IconBtn
        size="x-small"
        rounded="sm"
        :variant="editor.isActive({ textAlign: 'right' }) ? 'tonal' : 'text'"
        :color="editor.isActive({ textAlign: 'right' }) ? 'primary' : 'default'"
        title="Rata Kanan"
        @click="editor.chain().focus().setTextAlign('right').run()"
      >
        <VIcon icon="mdi-format-align-right" size="16" />
      </IconBtn>
      <IconBtn
        size="x-small"
        rounded="sm"
        :variant="editor.isActive({ textAlign: 'justify' }) ? 'tonal' : 'text'"
        :color="editor.isActive({ textAlign: 'justify' }) ? 'primary' : 'default'"
        title="Rata Kanan-Kiri"
        @click="editor.chain().focus().setTextAlign('justify').run()"
      >
        <VIcon icon="mdi-format-align-justify" size="16" />
      </IconBtn>
    </div>

    <EditorContent
      ref="editorRef"
      :editor="editor"
      class="editor-content-area"
    />
  </div>
</template>

<style lang="scss">
.tiptap-editor-container {
  display: flex;
  flex-direction: column;
  background-color: rgb(var(--v-theme-surface));
  border-radius: 8px;

  .editor-toolbar {
    background-color: rgba(var(--v-theme-on-surface), 0.02);
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
  }

  .editor-content-area {
    .ProseMirror {
      padding: 0.75rem 1rem;
      min-height: 200px;
      max-height: 420px;
      overflow-y: auto;
      outline: none;
      font-size: 0.85rem;
      line-height: 1.6;
      color: rgb(var(--v-theme-on-surface));

      h1, h2, h3, h4, h5, h6 {
        font-weight: 600;
        margin-top: 0.75rem;
        margin-bottom: 0.35rem;
        color: rgb(var(--v-theme-on-surface));
      }

      h2 {
        font-size: 1.125rem;
        border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
        padding-bottom: 0.25rem;
      }

      h3 {
        font-size: 0.95rem;
      }

      p {
        margin-bottom: 0.5rem;
      }

      ul, ol {
        padding-left: 1.25rem;
        margin-bottom: 0.5rem;

        li {
          margin-bottom: 0.15rem;
        }
      }

      blockquote {
        border-left: 3px solid rgb(var(--v-theme-primary));
        background: rgba(var(--v-theme-primary), 0.04);
        padding: 0.5rem 0.75rem;
        margin: 0.75rem 0;
        border-radius: 0 4px 4px 0;
        font-style: italic;
        font-size: 0.825rem;

        p {
          margin-bottom: 0;
        }
      }

      code {
        background-color: rgba(var(--v-theme-on-surface), 0.06);
        color: rgb(var(--v-theme-primary));
        padding: 0.1rem 0.3rem;
        border-radius: 3px;
        font-size: 0.82em;
        font-family: monospace;
      }

      pre {
        background: #1e1e2e;
        color: #cdd6f4;
        padding: 0.75rem;
        border-radius: 6px;
        overflow-x: auto;
        margin: 0.75rem 0;
        font-size: 0.8rem;

        code {
          background: transparent;
          color: inherit;
          padding: 0;
        }
      }

      hr {
        border: none;
        border-top: 1px dashed rgba(var(--v-border-color), var(--v-border-opacity));
        margin: 1rem 0;
      }

      p.is-editor-empty:first-child::before {
        content: attr(data-placeholder);
        float: left;
        color: rgba(var(--v-theme-on-surface), 0.38);
        pointer-events: none;
        height: 0;
        font-size: 0.85rem;
      }
    }
  }
}
</style>
