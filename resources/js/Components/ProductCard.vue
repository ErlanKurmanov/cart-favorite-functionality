<script>
import { Link } from '@inertiajs/vue3'

export default {
  name: 'ProductCard',
  components: {
    Link
  },
  props: {
    product: {
      type: Object,
      required: true
    }
  },
  emits: ['add-to-cart', 'toggle-favorite'],
  data() {
    return {
      isLoading: false
    }
  },
  methods: {
    formatPrice(price) {
      return parseFloat(price).toFixed(2)
    },
    
    truncateDescription(description, maxLength = 120) {
      if (description.length <= maxLength) {
        return description
      }
      return description.substring(0, maxLength) + '...'
    },

    handleImageError(event) {
      event.target.src = '/images/placeholder.jpg'
    }
  }
}
</script>

<template>
  <div class="product-card">
    <div class="product-image">
      <img 
        :src="product.image_url || '/images/placeholder.jpg'" 
        :alt="product.name"
        @error="handleImageError"
      />
      <button 
        @click="$emit('toggle-favorite', product)"
        :class="['favorite-btn', { active: product.is_favorite }]"
        :title="product.is_favorite ? 'Remove from favorites' : 'Add to favorites'"
      >
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
        </svg>
      </button>
    </div>

    <div class="product-info">
      <h3 class="product-name">{{ product.name }}</h3>
      <p class="product-description" v-if="product.description">
        {{ truncateDescription(product.description) }}
      </p>
      <div class="product-price">
        ${{ formatPrice(product.price) }}
      </div>
    </div>

    <div class="product-actions">
      <button 
        @click="$emit('add-to-cart', product)"
        class="add-to-cart-btn"
        :disabled="isLoading"
      >
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="9" cy="21" r="1"></circle>
          <circle cx="20" cy="21" r="1"></circle>
          <path d="m1 1 4 4 10 10 6-6"></path>
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
        </svg>
        {{ isLoading ? 'Adding...' : 'Add to Cart' }}
      </button>
      
      <Link 
        :href="`/products/${product.id}`" 
        class="view-details-btn"
      >
        View Details
      </Link>
    </div>
  </div>
</template>


<style scoped>
.product-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  overflow: hidden;
  transition: all 0.3s ease;
  position: relative;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.product-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.product-image {
  position: relative;
  width: 100%;
  height: 200px;
  overflow: hidden;
  background-color: #f8f9fa;
}

.product-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.product-card:hover .product-image img {
  transform: scale(1.05);
}

.favorite-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  background: white;
  border: none;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  color: #ccc;
}

.favorite-btn:hover {
  transform: scale(1.1);
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.favorite-btn.active {
  color: #e74c3c;
  background-color: #ffe6e6;
}

.product-info {
  padding: 1.5rem;
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

.product-name {
  font-size: 1.3rem;
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 0.8rem;
  line-height: 1.4;
}

.product-description {
  color: #6c757d;
  font-size: 0.95rem;
  line-height: 1.5;
  margin-bottom: 1rem;
  flex-grow: 1;
}

.product-price {
  font-size: 1.5rem;
  font-weight: 700;
  color: #27ae60;
  margin-bottom: 1rem;
}

.product-actions {
  padding: 0 1.5rem 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
}

.add-to-cart-btn {
  background: linear-gradient(135deg, #3498db, #2980b9);
  color: white;
  border: none;
  padding: 0.8rem 1.2rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  font-size: 0.95rem;
}

.add-to-cart-btn:hover {
  background: linear-gradient(135deg, #2980b9, #1f5f8b);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
}

.add-to-cart-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

.view-details-btn {
  background: transparent;
  color: #3498db;
  border: 2px solid #3498db;
  padding: 0.8rem 1.2rem;
  border-radius: 8px;
  text-decoration: none;
  text-align: center;
  font-weight: 600;
  transition: all 0.3s ease;
  font-size: 0.95rem;
}

.view-details-btn:hover {
  background-color: #3498db;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
}

/* Responsive Design */
@media (max-width: 768px) {
  .product-image {
    height: 180px;
  }
  
  .product-info {
    padding: 1.2rem;
  }
  
  .product-actions {
    padding: 0 1.2rem 1.2rem;
  }
  
  .product-name {
    font-size: 1.2rem;
  }
  
  .product-price {
    font-size: 1.3rem;
  }
}

@media (max-width: 480px) {
  .product-image {
    height: 160px;
  }
  
  .product-info {
    padding: 1rem;
  }
  
  .product-actions {
    padding: 0 1rem 1rem;
  }
  
  .add-to-cart-btn,
  .view-details-btn {
    padding: 0.7rem 1rem;
    font-size: 0.9rem;
  }
}
</style>