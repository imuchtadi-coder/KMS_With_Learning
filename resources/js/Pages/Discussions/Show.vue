<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  discussion: { type: Object, required: true },
  course: { type: Object, default: null },
})

const replyForm = useForm({ content: '' })

const submitReply = () => {
  replyForm.post(route('discussions.replies.store', props.discussion.id), {
    preserveScroll: true,
    onSuccess: () => replyForm.reset(),
  })
}
</script>

<template>
  <div>
    <VCard flat border class="pa-6 mb-6">
      <div class="d-flex align-center ga-3 mb-4">
        <VAvatar size="40" :color="discussion.creator?.avatar_url ? undefined : 'primary'" variant="tonal">
          <VImg v-if="discussion.creator?.avatar_url" :src="discussion.creator.avatar_url" />
          <span v-else class="text-uppercase font-weight-bold">{{ discussion.creator?.name?.[0] }}</span>
        </VAvatar>
        <div>
          <div class="font-weight-semibold">{{ discussion.creator?.name }}</div>
          <div class="text-caption text-medium-emphasis">{{ new Date(discussion.created_at).toLocaleString() }}</div>
        </div>
        <VChip v-if="discussion.is_pinned" color="primary" size="x-small" variant="tonal" label class="ml-auto">Pinned</VChip>
      </div>
      <h4 class="text-h4 font-weight-bold mb-3">{{ discussion.title }}</h4>
      <div class="text-body-1" style="white-space: pre-wrap;">{{ discussion.content }}</div>
    </VCard>

    <VCard flat border class="pa-6 mb-6">
      <h5 class="text-h5 font-weight-bold mb-4">Replies ({{ discussion.replies?.length ?? 0 }})</h5>
      <div v-if="discussion.replies?.length" class="d-flex flex-column ga-4">
        <div v-for="reply in discussion.replies" :key="reply.id" class="d-flex ga-3">
          <VAvatar size="36" :color="reply.creator?.avatar_url ? undefined : 'secondary'" variant="tonal">
            <VImg v-if="reply.creator?.avatar_url" :src="reply.creator.avatar_url" />
            <span v-else class="text-uppercase font-weight-bold">{{ reply.creator?.name?.[0] }}</span>
          </VAvatar>
          <div class="flex-grow-1">
            <div class="d-flex align-center ga-2">
              <span class="font-weight-semibold text-body-2">{{ reply.creator?.name }}</span>
              <span class="text-caption text-medium-emphasis">{{ new Date(reply.created_at).toLocaleString() }}</span>
            </div>
            <div class="text-body-2 mt-1" style="white-space: pre-wrap;">{{ reply.content }}</div>
          </div>
        </div>
      </div>
      <p v-else class="text-body-2 text-medium-emphasis text-center py-4">No replies yet. Be the first to reply!</p>
    </VCard>

    <VCard flat border class="pa-6">
      <h5 class="text-h5 font-weight-bold mb-3">Post a Reply</h5>
      <VForm @submit.prevent="submitReply">
        <VTextarea
          v-model="replyForm.content"
          variant="outlined"
          density="default"
          placeholder="Write your reply..."
          rows="3"
          :rules="[v => !!v || 'Reply cannot be empty']"
          hide-details="auto"
          class="mb-3"
        />
        <VBtn
          type="submit"
          color="primary"
          variant="elevated"
          :loading="replyForm.processing"
          :disabled="replyForm.processing"
        >
          Post Reply
        </VBtn>
      </VForm>
    </VCard>
  </div>
</template>
