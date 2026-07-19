import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import LandingView from '../views/LandingView.vue'
import MenuView from '../views/MenuView.vue'
import ChatView from '../views/ChatView.vue'
import CartView from '../views/CartView.vue'
import AdminLoginView from '../views/admin/AdminLoginView.vue'
import AdminOrdersView from '../views/admin/AdminOrdersView.vue'
import AdminTablesView from '../views/admin/AdminTablesView.vue'
import AdminCashRegisterView from '../views/admin/AdminCashRegisterView.vue'
import AdminMenuView from '../views/admin/AdminMenuView.vue'
import AdminAiCostsView from '../views/admin/AdminAiCostsView.vue'
import { useAdminAuthStore } from '../stores/adminAuth.js'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/t/:qrToken', name: 'landing', component: LandingView },
    { path: '/menu', name: 'menu', component: MenuView },
    { path: '/chat', name: 'chat', component: ChatView },
    { path: '/cart', name: 'cart', component: CartView },
    { path: '/admin/login', name: 'admin-login', component: AdminLoginView },
    {
      path: '/admin/orders',
      name: 'admin-orders',
      component: AdminOrdersView,
      meta: { requiresAdmin: true },
    },
    {
      path: '/admin/tables',
      name: 'admin-tables',
      component: AdminTablesView,
      meta: { requiresAdmin: true },
    },
    {
      path: '/admin/cash-register',
      name: 'admin-cash-register',
      component: AdminCashRegisterView,
      meta: { requiresAdmin: true },
    },
    {
      path: '/admin/menu',
      name: 'admin-menu',
      component: AdminMenuView,
      meta: { requiresAdmin: true },
    },
    {
      path: '/admin/ai-costs',
      name: 'admin-ai-costs',
      component: AdminAiCostsView,
      meta: { requiresAdmin: true },
    },
    { path: '/', name: 'home', component: HomeView },
  ],
})

// Rotte /admin/* (tranne il login) richiedono un token Sanctum valido:
// senza, si viene rediretti al login admin.
router.beforeEach((to) => {
  if (to.meta.requiresAdmin) {
    const adminAuth = useAdminAuthStore()
    if (!adminAuth.isAuthenticated) {
      return { name: 'admin-login' }
    }
  }
})

export default router
