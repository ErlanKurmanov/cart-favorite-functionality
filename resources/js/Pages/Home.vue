<script>
import { Link, router } from '@inertiajs/vue3'
import ProductCard from '@/Components/ProductCard.vue'
import NotificationToast from '@/Components/NotificationToast.vue'

export default {
  name: 'ProductsPage',
  components: {
    Link,
    ProductCard,
    NotificationToast,
  },
  props: {
    products: Array,
    categories: Array,
    auth: Object
  },
  data() {
    return {
      selectedCategory: null,
      message: '',
      messageType: 'success',
      showNotification: false,
      cartCount: 0,
      pusherChannel: null
    }
  },
  computed: {
    filteredProducts() {
      if (!this.selectedCategory) {
        return this.products
      }
      return this.products.filter(product => product.category_id === this.selectedCategory)
    }
  },
  mounted() {
    this.initializePusher()
  },
  beforeUnmount() {
    this.disconnectPusher()
  },
  methods: {
    initializePusher() {
      if (window.Echo && this.auth?.user) {
        // Subscribe to private channel for the authenticated user
        this.pusherChannel = window.Echo.private(`cart.${this.auth.user.id}`)
        
        // Listen for item added to cart events
        this.pusherChannel.listen('.item.added', (data) => {
          this.handleCartNotification(data)
        })
        
        console.log('Pusher initialized for user:', this.auth.user.id)
      }
    },
    
    disconnectPusher() {
      if (this.pusherChannel) {
        window.Echo.leave(`cart.${this.auth.user.id}`)
        this.pusherChannel = null
      }
    },
    
    handleCartNotification(data) {
      this.cartCount = data.cart_count || 0
      this.showMessage(data.message || 'Item added to cart!', 'success')
      
      // Optional: Add visual feedback like cart icon animation
      this.animateCartIcon()
    },
    
    animateCartIcon() {
      const cartLink = document.querySelector('.cart-link')
      if (cartLink) {
        cartLink.classList.add('cart-bounce')
        setTimeout(() => {
          cartLink.classList.remove('cart-bounce')
        }, 600)
      }
    },

    getCategoryName(categoryId) {
      const category = this.categories.find(cat => cat.id === categoryId)
      return category ? category.name : ''
    },
    
    addToCart(product) {
      router.post('/cart/add', {
        product_id: product.id,
        quantity: 1
      }, {
        preserveScroll: true,
        onSuccess: () => {
          // The success message will now come from Pusher
          // this.showMessage('Product added to cart!', 'success')
        },
        onError: () => {
          this.showMessage('Failed to add product to cart', 'error')
        }
      })
    },

    toggleFavorite(product) {
      const isFavorite = product.is_favorite
      const url = isFavorite ? `/favorites/remove/${product.id}` : `/favorites/add/${product.id}`
      
      router.post(url, {}, {
        preserveScroll: true,
        onSuccess: () => {
          product.is_favorite = !isFavorite
          const action = isFavorite ? 'removed from' : 'added to'
          this.showMessage(`Product ${action} favorites!`, 'success')
        },
        onError: () => {
          this.showMessage('Failed to update favorites', 'error')
        }
      })
    },

    showMessage(text, type = 'success') {
      this.message = text
      this.messageType = type
      this.showNotification = true
    },
    
    closeNotification() {
      this.showNotification = false
      this.message = ''
    }
  }
}
</script>

<template>
  <div class="products-page">
    <header class="header">
        <div class="container">
        <h1 class="logo">Shop</h1>
        <nav class="nav">
            <Link href="/dashboard" class="dashboard-link">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 3h18v18H3V3z"></path>
                <path d="M9 9h6v6H9V9z"></path>
              </svg>
              Dashboard/Profile
            </Link>

            <Link href="/cart" class="cart-link">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="m1 1 4 4 2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
              </svg>
              Cart
              <span v-if="cartCount > 0" class="cart-badge">{{ cartCount }}</span>
            </Link>
            <Link href="/favorites" class="favorites-link">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"></path>
              </svg>
              Favorites
            </Link>
        </nav>
        </div>
    </header>

    <main class="main">
      <div class="container">
        <!-- Categories Filter -->
        <div class="categories-section">
          <h2>Shop by Category</h2>
          <div class="categories-grid">
            <button 
              @click="selectedCategory = null" 
              :class="['category-btn', { active: selectedCategory === null }]"
            >
              All Products
            </button>
            <button 
              v-for="category in categories" 
              :key="category.id"
              @click="selectedCategory = category.id"
              :class="['category-btn', { active: selectedCategory === category.id }]"
            >
              {{ category.name }}
            </button>
          </div>
        </div>

        <!-- Products Grid -->
        <div class="products-section">
          <h2 v-if="selectedCategory">
            {{ getCategoryName(selectedCategory) }}
          </h2>
          <h2 v-else>All Products</h2>
          
          <div class="products-grid">
            <ProductCard 
              v-for="product in filteredProducts" 
              :key="product.id"
              :product="product"
              @add-to-cart="addToCart"
              @toggle-favorite="toggleFavorite"
            />
          </div>

          <div v-if="filteredProducts.length === 0" class="no-products">
            <p>No products found in this category.</p>
          </div>
        </div>
      </div>
    </main>

    <!-- Notification Toast -->
    <NotificationToast 
      :show="showNotification"
      :message="message"
      :type="messageType"
      @close="closeNotification"
    />
  </div>
</template>

<style scoped>
/* Global Styles */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.products-page {
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
  position: sticky;
  top: 0;
  z-index: 100;
}

.header .container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 20px;
}

.logo {
  font-size: 2rem;
  font-weight: bold;
  color: #2c3e50;
}

.nav {
  display: flex;
  gap: 1.5rem;
}

.cart-link, .favorites-link {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
  color: #3498db;
  font-weight: 500;
  padding: 0.5rem 1rem;
  border: 2px solid #3498db;
  border-radius: 5px;
  transition: all 0.3s ease;
  position: relative;
}

.cart-link:hover, .favorites-link:hover {
  background-color: #3498db;
  color: white;
}

.cart-badge {
  position: absolute;
  top: -8px;
  right: -8px;
  background: #e74c3c;
  color: white;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: bold;
}

/* Cart Animation */
@keyframes cartBounce {
  0%, 20%, 60%, 100% {
    transform: translateY(0);
  }
  40% {
    transform: translateY(-10px);
  }
  80% {
    transform: translateY(-5px);
  }
}

.cart-bounce {
  animation: cartBounce 0.6s ease;
}

/* Main Content */
.main {
  padding: 2rem 0;
}

/* Categories Section */
.categories-section {
  margin-bottom: 3rem;
}

.categories-section h2 {
  margin-bottom: 1.5rem;
  font-size: 1.8rem;
  color: #2c3e50;
}

.categories-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
}

.category-btn {
  background: white;
  border: 2px solid #e0e6ed;
  padding: 0.8rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 500;
  color: #495057;
  transition: all 0.3s ease;
}

.category-btn:hover {
  border-color: #3498db;
  color: #3498db;
}

.category-btn.active {
  background-color: #3498db;
  border-color: #3498db;
  color: white;
}

/* Products Section */
.products-section h2 {
  margin-bottom: 2rem;
  font-size: 1.8rem;
  color: #2c3e50;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 2rem;
  margin-bottom: 2rem;
}

.no-products {
  text-align: center;
  padding: 3rem;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.no-products p {
  font-size: 1.2rem;
  color: #6c757d;
}

/* Responsive Design */
@media (max-width: 768px) {
  .header .container {
    flex-direction: column;
    gap: 1rem;
  }

  .nav {
    width: 100%;
    justify-content: center;
  }

  .categories-grid {
    justify-content: center;
  }

  .products-grid {
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
  }

  .container {
    padding: 0 15px;
  }
}

@media (max-width: 480px) {
  .products-grid {
    grid-template-columns: 1fr;
  }
  
  .logo {
    font-size: 1.5rem;
  }
}
</style>