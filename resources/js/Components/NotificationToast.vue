<script>
export default {
  name: 'NotificationToast',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    message: {
      type: String,
      default: ''
    },
    type: {
      type: String,
      default: 'success' // success, error, info, warning
    },
    duration: {
      type: Number,
      default: 4000
    }
  },
  emits: ['close'],
  watch: {
    show(newVal) {
      if (newVal) {
        setTimeout(() => {
          this.$emit('close')
        }, this.duration)
      }
    }
  },
  methods: {
    closeNotification() {
      this.$emit('close')
    }
  }
}
</script>

<template>
  <Teleport to="body">
    <Transition name="toast">
      <div v-if="show" :class="['toast-notification', type]">
        <div class="toast-content">
          <div class="toast-icon">
            <!-- Success Icon -->
            <svg v-if="type === 'success'" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M9 12l2 2 4-4"/>
              <circle cx="12" cy="12" r="10"/>
            </svg>
            
            <!-- Error Icon -->
            <svg v-else-if="type === 'error'" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <line x1="15" y1="9" x2="9" y2="15"/>
              <line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
            
            <!-- Info Icon -->
            <svg v-else-if="type === 'info'" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <line x1="12" y1="16" x2="12" y2="12"/>
              <line x1="12" y1="8" x2="12.01" y2="8"/>
            </svg>
            
            <!-- Warning Icon -->
            <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
              <line x1="12" y1="9" x2="12" y2="13"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </div>
          
          <div class="toast-message">
            {{ message }}
          </div>
          
          <button @click="closeNotification" class="toast-close">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"/>
              <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.toast-notification {
  position: fixed;
  top: 100px;
  right: 20px;
  z-index: 9999;
  min-width: 300px;
  max-width: 500px;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.toast-content {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  color: white;
}

.toast-icon {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.toast-message {
  flex: 1;
  font-weight: 500;
  font-size: 14px;
  line-height: 1.4;
}

.toast-close {
  flex-shrink: 0;
  background: none;
  border: none;
  color: currentColor;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  transition: background-color 0.2s ease;
}

.toast-close:hover {
  background-color: rgba(255, 255, 255, 0.2);
}

/* Toast Types */
.toast-notification.success {
  background: linear-gradient(135deg, #28a745, #20c997);
}

.toast-notification.error {
  background: linear-gradient(135deg, #dc3545, #e74c3c);
}

.toast-notification.info {
  background: linear-gradient(135deg, #17a2b8, #3498db);
}

.toast-notification.warning {
  background: linear-gradient(135deg, #ffc107, #f39c12);
  color: #212529;
}

/* Animations */
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%) scale(0.9);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%) scale(0.9);
}

.toast-enter-to,
.toast-leave-from {
  opacity: 1;
  transform: translateX(0) scale(1);
}

/* Responsive */
@media (max-width: 480px) {
  .toast-notification {
    left: 20px;
    right: 20px;
    min-width: auto;
  }
}
</style>