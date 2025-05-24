    <!-- Header -->
<template>
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
</template>
