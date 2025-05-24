<script>
import { Link, router } from '@inertiajs/vue3'
import ProductCard from '@/Components/ProductCard.vue'

export default {
  name: 'ProductsPage',
  components: {
    Link,
    ProductCard
  },
  props: {
    products: Array,
    categories: Array,
    cartItemsCount: {
      type: Number,
      default: 0
    }
  },
  data() {
    return {
      selectedCategory: null,
      message: '',
      messageType: 'success'
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
  methods: {
    getCategoryName(categoryId) {
      const category = this.categories.find(cat => cat.id === categoryId)
      return category ? category.name : ''
    },
    
    addToCart(product) {
      router.post('/cart/add', {
        product_id: product.id,
        quantity: 1
      }, {
        onSuccess: () => {
          this.showMessage('Product added to cart!', 'success')
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
      setTimeout(() => {
        this.message = ''
      }, 3000)
    }
  }
}
</script>

<template>
  <div class="products-page">
    <!-- Header -->
    <header class="header">
      <div class="container">
        <h1 class="logo">Shop</h1>
        <nav class="nav">
          <Link href="/cart" class="cart-link">
            Cart ({{ cartItemsCount }})
          </Link>
          <Link href="/favorites" class="favorites-link">
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

    <!-- Success/Error Messages -->
    <div v-if="message" :class="['message', messageType]">
      {{ message }}
    </div>
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
  text-decoration: none;
  color: #3498db;
  font-weight: 500;
  padding: 0.5rem 1rem;
  border: 2px solid #3498db;
  border-radius: 5px;
  transition: all 0.3s ease;
}

.cart-link:hover, .favorites-link:hover {
  background-color: #3498db;
  color: white;
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

/* Message Styles */
.message {
  position: fixed;
  top: 20px;
  right: 20px;
  padding: 1rem 1.5rem;
  border-radius: 5px;
  color: white;
  font-weight: 500;
  z-index: 1000;
  animation: slideIn 0.3s ease;
}

.message.success {
  background-color: #27ae60;
}

.message.error {
  background-color: #e74c3c;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
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