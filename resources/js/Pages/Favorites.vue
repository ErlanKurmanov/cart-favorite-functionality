<script>
import { Link, router } from '@inertiajs/vue3'
import ProductCard from '@/Components/ProductCard.vue'

export default {
  name: 'FavoritesPage',
  components: {
    Link,
    ProductCard
  },
  props: {
    favorites: Array,
    categories: Array
  },
  data() {
    return {
      selectedCategory: null,
      message: '',
      messageType: 'success'
    }
  },
  computed: {
    filteredFavorites() {
      if (!this.selectedCategory) {
        return this.favorites
      }
      return this.favorites.filter(product => product.category_id === this.selectedCategory)
    },
    availableCategories() {
      // Only show categories that have favorite products
      const categoryIds = [...new Set(this.favorites.map(product => product.category_id))]
      return this.categories.filter(category => categoryIds.includes(category.id))
    }
  },
  methods: {
    // getCategoryName(categoryId) {
    //   const category = this.categories.find(cat => cat.id === categoryId)
    //   return category ? category.name : ''
    // },
    
    addToCart(product) {
      router.post('/cart/add', {
        product_id: product.id,
        quantity: 1
      }, {
        preserveScroll: true,
        onSuccess: () => {
          this.showMessage('Product added to cart!', 'success')
        },
        onError: () => {
          this.showMessage('Failed to add product to cart', 'error')
        }
      })
    },

    removeFromFavorites(product) {
      router.post(`/favorites/remove/${product.id}`, {}, {
        preserveScroll: true,
        onSuccess: () => {
          // Remove the product from the local favorites array
          const index = this.favorites.findIndex(fav => fav.id === product.id)
          if (index > -1) {
            this.favorites.splice(index, 1)
          }
          this.showMessage('Product removed from favorites!', 'success')
          
          // Reset category filter if no products left in selected category
          if (this.selectedCategory && this.filteredFavorites.length === 0) {
            this.selectedCategory = null
          }
        },
        onError: () => {
          this.showMessage('Failed to remove from favorites', 'error')
        }
      })
    },

    clearAllFavorites() {
      if (confirm('Are you sure you want to remove all products from favorites?')) {
        router.post('/favorites/clear', {}, {
          onSuccess: () => {
            this.favorites.splice(0, this.favorites.length)
            this.selectedCategory = null
            this.showMessage('All favorites cleared!', 'success')
          },
          onError: () => {
            this.showMessage('Failed to clear favorites', 'error')
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
  }
}
</script>

<template>
  <div class="favorites-page">
    <!-- Header -->
    <header class="header">
      <div class="container">
        <h1 class="logo">
          <Link href="/">Shop</Link>
        </h1>
        <nav class="nav">
          <Link href="/cart" class="cart-link">
            Cart
          </Link>
        </nav>
      </div>
    </header>

    <main class="main">
      <div class="container">
        <!-- Page Title and Actions -->
        <div class="favorites-header">
          <h1>My Favorites</h1>
          <div class="header-actions">
            <span class="favorites-count">{{ favorites.length }} item{{ favorites.length !== 1 ? 's' : '' }}</span>
            <button 
              v-if="favorites.length > 0" 
              @click="clearAllFavorites" 
              class="clear-all-btn"
            >
              Clear All
            </button>
          </div>
        </div>

        <!-- Categories Filter (only show if there are favorites) -->
        <div v-if="favorites.length > 0 && availableCategories.length > 1" class="categories-section">
          <h2>Filter by Category</h2>
          <div class="categories-grid">
            <button 
              @click="selectedCategory = null" 
              :class="['category-btn', { active: selectedCategory === null }]"
            >
              All Favorites ({{ favorites.length }})
            </button>
            <button 
              v-for="category in availableCategories" 
              :key="category.id"
              @click="selectedCategory = category.id"
              :class="['category-btn', { active: selectedCategory === category.id }]"
            >
              {{ category.name }} ({{ favorites.filter(p => p.category_id === category.id).length }})
            </button>
          </div>
        </div>

        <!-- Favorites Grid -->
        <div class="favorites-section">
          <div v-if="favorites.length === 0" class="empty-favorites">
            <div class="empty-icon">♡</div>
            <h2>No favorites yet</h2>
            <p>Products you favorite will appear here for easy access.</p>
            <Link href="/" class="browse-products-btn">
              Browse Products
            </Link>
          </div>

          <div v-else>
            <h2 v-if="selectedCategory">
              {{ getCategoryName(selectedCategory) }} Favorites
            </h2>
            <h2 v-else-if="availableCategories.length <= 1">
              Your Favorite Products
            </h2>
            
            <div class="products-grid">
              <div 
                v-for="product in filteredFavorites" 
                :key="product.id"
                class="favorite-product-wrapper"
              >
                <ProductCard 
                  :product="product"
                  @add-to-cart="addToCart"
                  @toggle-favorite="removeFromFavorites"
                />
                <button 
                  @click="removeFromFavorites(product)"
                  class="remove-favorite-btn"
                  title="Remove from favorites"
                >
                  ✕
                </button>
              </div>
            </div>

            <div v-if="filteredFavorites.length === 0 && selectedCategory" class="no-products">
              <p>No favorite products found in this category.</p>
              <button @click="selectedCategory = null" class="show-all-btn">
                Show All Favorites
              </button>
            </div>
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
.favorites-page {
  min-height: 100vh;
  background-color: #f9fafb;
}

.header {
  background: white;
  border-bottom: 1px solid #e5e7eb;
  padding: 1rem 0;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

.header .container {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.logo {
  font-size: 2rem;
  font-weight: bold;
  color: #2c3e50;
}

.nav {
  display: flex;
  gap: 1.5rem;
  align-items: center;
}

.nav a, .nav span {
  color: #6b7280;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s;
}

.nav a:hover {
  color: #1f2937;
}

.nav .active {
  color: #3b82f6;
}

.cart-link {
  text-decoration: none;
  color: #3498db;
  font-weight: 500;
  padding: 0.5rem 1rem;
  border: 2px solid #3498db;
  border-radius: 5px;
  transition: all 0.3s ease;
}

.cart-link:hover {
  background-color: #3498db;
  color: white;
}

.main {
  padding: 2rem 0;
}

.favorites-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #e5e7eb;
}

.favorites-header h1 {
  font-size: 2rem;
  font-weight: bold;
  color: #1f2937;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.favorites-count {
  color: #6b7280;
  font-size: 0.9rem;
}

.clear-all-btn {
  background: #ef4444;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.clear-all-btn:hover {
  background: #dc2626;
}

.categories-section {
  margin-bottom: 2rem;
}

.categories-section h2 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 1rem;
}

.categories-grid {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.category-btn {
  background: white;
  border: 2px solid #e5e7eb;
  color: #6b7280;
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.category-btn:hover {
  border-color: #d1d5db;
  color: #374151;
}

.category-btn.active {
  background: #3b82f6;
  border-color: #3b82f6;
  color: white;
}

.empty-favorites {
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 0.75rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.empty-icon {
  font-size: 4rem;
  color: #d1d5db;
  margin-bottom: 1rem;
}

.empty-favorites h2 {
  font-size: 1.5rem;
  color: #6b7280;
  margin-bottom: 0.5rem;
}

.empty-favorites p {
  color: #9ca3af;
  margin-bottom: 2rem;
}

.browse-products-btn {
  background: #3b82f6;
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  text-decoration: none;
  font-weight: 500;
  display: inline-block;
  transition: background-color 0.2s;
}

.browse-products-btn:hover {
  background: #2563eb;
}

.favorites-section h2 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 1.5rem;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
}

.favorite-product-wrapper {
  position: relative;
}

.remove-favorite-btn {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  background: rgba(239, 68, 68, 0.9);
  color: white;
  border: none;
  width: 2rem;
  height: 2rem;
  border-radius: 50%;
  font-size: 0.875rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.2s;
  z-index: 10;
}

.remove-favorite-btn:hover {
  background: rgba(220, 38, 38, 0.9);
}

.no-products {
  text-align: center;
  padding: 3rem 2rem;
  background: white;
  border-radius: 0.75rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.no-products p {
  color: #6b7280;
  margin-bottom: 1rem;
}

.show-all-btn {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.show-all-btn:hover {
  background: #2563eb;
}

.message {
  position: fixed;
  bottom  : 1rem;
  right: 1rem;
  padding: 1rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: 500;
  z-index: 1000;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.message.success {
  background: #10b981;
  color: white;
}

.message.error {
  background: #ef4444;
  color: white;
}

@media (max-width: 768px) {
  .favorites-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  .header-actions {
    width: 100%;
    justify-content: space-between;
  }

  .products-grid {
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1rem;
  }

  .categories-grid {
    flex-direction: column;
  }

  .category-btn {
    text-align: left;
  }
}
</style>