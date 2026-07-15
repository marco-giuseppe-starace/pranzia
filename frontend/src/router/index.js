import { createRouter, createWebHistory } from 'vue-router'
import LandingView from '../views/LandingView.vue'
import MenuView from '../views/MenuView.vue'
import ChatView from '../views/ChatView.vue'
import CartView from '../views/CartView.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/t/:qrToken', name: 'landing', component: LandingView },
    { path: '/menu', name: 'menu', component: MenuView },
    { path: '/chat', name: 'chat', component: ChatView },
    { path: '/cart', name: 'cart', component: CartView },
    { path: '/', redirect: '/menu' },
  ],
})

export default router
