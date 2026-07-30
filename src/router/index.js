import { createRouter, createWebHistory } from "vue-router";
import MainLayout from "../layouts/MainLayout.vue";
import AuthLayout from "../layouts/AuthLayout.vue";

const routes = [
  {
    path: "/",
    component: AuthLayout,
    children: [
      {
        path: "",
        name: "login",
        component: () => import("../views/auth/LoginView.vue"),
      },
    ],
  },
  {
    path: "/",
    component: MainLayout,
    children: [
      {
        path: "customers",
        name: "customers.index",
        component: () => import("../views/customers/CustomersList.vue"),
      },
      {
        path: "orders/create",
        name: "orders.create",
        component: () => import("../views/orders/CreateOrder.vue"),
      },
      {
        path: "products/create",
        name: "products.create",
        component: () => import("../views/products/CreateProduct.vue"),
      },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
