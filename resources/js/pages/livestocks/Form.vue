<template>
  <div class="livestock-form">
    <div class="modal-overlay" v-if="showModal" @click.self="closeModal">
      <div class="modal-content">
        <h2 class="text-xl font-bold mb-4">{{ isEditing ? 'Edit' : 'Add' }} Livestock</h2>
        
        <form @submit.prevent="handleSubmit">
          <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">
              Name
            </label>
            <input
              v-model="form.name"
              type="text"
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
              required
            >
          </div>

          <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">
              Type
            </label>
            <select
              v-model="form.type"
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
              required
            >
              <option value="">Select type</option>
              <option value="cow">Cow</option>
              <option value="sheep">Sheep</option>
              <option value="goat">Goat</option>
              <option value="chicken">Chicken</option>
            </select>
          </div>

          <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">
              Age
            </label>
            <input
              v-model.number="form.age"
              type="number"
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
              required
              min="0"
            >
          </div>

          <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">
              Weight (kg)
            </label>
            <input
              v-model.number="form.weight"
              type="number"
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
              required
              min="0"
            >
          </div>

          <div class="flex justify-end space-x-4">
            <button
              type="button"
              @click="closeModal"
              class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
            >
              {{ isEditing ? 'Update' : 'Create' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    show: Boolean,
    livestock: Object
  },
  data() {
    return {
      form: {
        name: '',
        type: '',
        age: null,
        weight: null
      }
    }
  },
  computed: {
    showModal() {
      return this.show
    },
    isEditing() {
      return !!this.livestock
    }
  },
  watch: {
    livestock: {
      immediate: true,
      handler(newVal) {
        if (newVal) {
          this.form = { ...newVal }
        } else {
          this.resetForm()
        }
      }
    }
  },
  methods: {
    closeModal() {
      this.$emit('close')
    },
    resetForm() {
      this.form = {
        name: '',
        type: '',
        age: null,
        weight: null
      }
    },
    async handleSubmit() {
      try {
        const url = this.isEditing 
          ? `/api/livestock/${this.livestock.id}` 
          : '/api/livestock'
        
        const method = this.isEditing ? 'put' : 'post'
        
        await axios[method](url, this.form)
        this.$emit('saved')
        this.closeModal()
      } catch (error) {
        console.error('Error saving livestock:', error)
      }
    }
  }
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background-color: white;
  padding: 2rem;
  border-radius: 8px;
  max-width: 500px;
  width: 90%;
}
</style>
