<script>
import { Link, router } from '@inertiajs/vue3'

export default {
  name: 'CartPage',
  components: {
    Link
  },
  props: {
    cart: Object
  },
  data() {
    return {
      orderComments: '',
      message: '',
      messageType: 'success'
    }
  },
  methods: {
    formatPrice(price) {
      return parseFloat(price).toFixed(2)
    },

    handleImageError(event) {
      event.target.src = '/images/placeholder.jpg'
    },

    updateQuantity(item, newQuantity) {
      if (newQuantity < 1) return
      
      router.patch(`/cart/update/${item.id}`, {
        quantity: newQuantity
      }, {
        onSuccess: () => {
          this.showMessage('Cart updated successfully', 'success')
        },
        onError: () => {
          this.showMessage('Failed to update cart', 'error')
        }
      })
    },

    removeItem(item) {
      if (confirm('Are you sure you want to remove this item from your cart?')) {
        router.delete(`/cart/remove/${item.product_id}`, {
          onSuccess: () => {
            this.showMessage('Item removed from cart', 'success')
          },
          onError: () => {
            this.showMessage('Failed to remove item', 'error')
          }
        })
      }
    },

    clearCart() {
      if (confirm('Are you sure you want to clear your entire cart?')) {
        router.delete('/cart/clear', {
          onSuccess: () => {
            this.showMessage('Cart cleared successfully', 'success')
          },
          onError: () => {
            this.showMessage('Failed to clear cart', 'error')
          }
        })
      }
    },

    showMessage(text, type = 'success') {
      this.message = text
      this.messageType = type
      setTimeout(() => {
        this.message = ''
      }, 3000)
    }
  },
    // mounted() {
    // console.log(this.cart)
    // }
}
</script>

<template>
  <div class="cart-page">
    <!-- Header -->
    <header class="header">
      <div class="container">
        <Link href="/" class="back-link">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="m15 18-6-6 6-6"/>
          </svg>
          Back to Shop
        </Link>
        <h1 class="page-title">Shopping Cart</h1>
      </div>
    </header>

    <main class="main">
      <div class="container">
        <!-- Cart Content -->
        <div v-if="cart.data && cart.data.items && cart.data.items.length > 0" class="cart-content">
          <div class="cart-items">
            <div class="cart-header">
              <h2>Your Items ({{ cart.data.items.length }})</h2>
            </div>

            <!-- Items list -->
            <div class="items-list">
              <div 
                v-for="item in cart.data.items" 
                :key="item.id"
                class="cart-item"
              >
                <div class="item-image">
                  <img 
                    :src="item.image_url || '/images/placeholder.jpg'" 
                    :alt="item.name"
                    @error="handleImageError"
                  />
                </div>

                <div class="item-details">
                  <h3 class="item-name">{{ item.name }}</h3>
                  <p class="item-price">${{ formatPrice(item.price) }} each</p>
                </div>

                <div class="quantity-controls">
                  <button 
                    @click="updateQuantity(item, item.quantity - 1)"
                    class="quantity-btn decrease"
                    :disabled="item.quantity <= 1"
                  >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M5 12h14"/>
                    </svg>
                  </button>
                  
                  <input 
                    type="number" 
                    :value="item.quantity"
                    @change="updateQuantity(item, parseInt($event.target.value))"
                    class="quantity-input"
                    min="1"
                    max="99"
                  />
                  
                  <button 
                    @click="updateQuantity(item, item.quantity + 1)"
                    class="quantity-btn increase"
                  >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M12 5v14m-7-7h14"/>
                    </svg>
                  </button>
                </div>

                <div class="item-total">
                 <label>Total:</label>${{ formatPrice(item.total) }}
                </div>

                <button 
                  @click="removeItem(item)"
                  class="remove-btn"
                  title="Remove item"
                >
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 6h18m-2 0v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                    <path d="M10 11v6m4-6v6"/>
                  </svg>
                </button>

                
              </div>
              <button @click="clearCart" class="clear-cart-btn">
                  Clear Cart
                </button>
            </div>
          </div>

        </div>

        <!-- Empty Cart State -->
        <div v-else class="empty-cart">
          <div class="empty-cart-content">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
              <circle cx="9" cy="21" r="1"></circle>
              <circle cx="20" cy="21" r="1"></circle>
              <path d="m1 1 4 4 10 10 6-6"></path>
              <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
            <h2>Your cart is empty</h2>
            <p>Add some products to get started!</p>
            <Link href="/" class="shop-now-btn">
              Start Shopping
            </Link>
          </div>
        </div>
      </div>
    </main>

    <!-- Success/Error Messages -->
    <div v-if="message" :class="['message', messageType]">
      {{ message }}
    </div>
  </div>
</template>



<style scoped>
/* Global Styles */
.cart-page {
  font-family: 'Arial', sans-serif;
  line-height: 1.6;
  color: #333;
  background-color: #f8f9fa;
  min-height: 100vh;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

/* Header Styles */
.header {
  background: white;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  padding: 1.5rem 0;
}

.header .container {
  display: flex;
  align-items: center;
  gap: 2rem;
}

.back-link {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
  color: #3498db;
  font-weight: 500;
  transition: color 0.3s ease;
}

.back-link:hover {
  color: #2980b9;
}

.page-title {
  font-size: 2rem;
  color: #2c3e50;
  margin: 0;
}

/* Main Content */
.main {
  padding: 2rem 0;
}

.cart-content {
  display: grid;
  grid-template-columns: 1fr 350px;
  gap: 2rem;
}

/* Cart Items */
.cart-items {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  overflow: hidden;
}

.cart-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e9ecef;
  background: #f8f9fa;
}

.cart-header h2 {
  margin: 0;
  color: #2c3e50;
  font-size: 1.5rem;
}

.items-list {
  padding: 1rem;
}

.cart-item {
  display: grid;
  grid-template-columns: 80px;
  gap: 1rem;
  align-items: center;
  padding: 1rem 0;
  border-bottom: 1px solid #e9ecef;
}

.item-image img {
  width: 100%;
  border-radius: 8px;
}

.item-details {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.item-name {
  margin: 0;
  font-weight: 600;
  font-size: 1rem;
}

.item-price {
  margin: 0;
  color: #6c757d;
  font-size: 0.9rem;
}

.quantity-controls {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.quantity-btn {
  background: #e9ecef;
  border: none;
  border-radius: 4px;
  padding: 0.25rem;
  cursor: pointer;
  transition: background 0.2s ease;
}

.quantity-btn:hover:not(:disabled) {
  background: #ced4da;
}

.quantity-btn:disabled {
  cursor: not-allowed;
  opacity: 0.5;
}

.quantity-input {
  width: 50px;
  text-align: center;
  border: 1px solid #ced4da;
  border-radius: 4px;
  padding: 0.25rem;
}

.item-total {
  font-weight: bold;
  font-size: 1rem;
}

.remove-btn {
  background: transparent;
  border: none;
  cursor: pointer;
  color: #dc3545;
  transition: color 0.2s ease;
}

.remove-btn:hover {
  color: #a71d2a;
}


.clear-cart-btn,
.continue-shopping-btn {
  display: block;
  width: 100%;
  margin-top: 0.5rem;
  text-align: center;
  padding: 0.5rem 1rem;
  font-weight: 500;
  border-radius: 8px;
  text-decoration: none;
  transition: background 0.3s ease;
}

.clear-cart-btn {
  background: #dc3545;
  color: white;
  border: none;
  cursor: pointer;
}

.clear-cart-btn:hover {
  background: #c82333;
}


/* Empty Cart */
.empty-cart {
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.empty-cart-content svg {
  margin-bottom: 1rem;
  color: #6c757d;
}

.shop-now-btn {
  display: inline-block;
  margin-top: 1rem;
  background: #007bff;
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  text-decoration: none;
  transition: background 0.3s ease;
}

.shop-now-btn:hover {
  background: #0056b3;
}

/* Message Styles */
.message {
  position: fixed;
  bottom: 1.5rem;
  right: 1.5rem;
  padding: 1rem 1.5rem;
  border-radius: 8px;
  color: white;
  font-weight: bold;
  z-index: 1000;
}

.message.success {
  background-color: #28a745;
}

.message.error {
  background-color: #dc3545;
}
</style>
