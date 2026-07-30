// src/router/index.js
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
        path: "dashboard",
        name: "dashboard.index",
        component: () => import("../views/dashboard/DashboardView.vue"),
      },
      {
        path: "customers",
        name: "customers.index",
        component: () => import("../views/customers/CustomersList.vue"),
      },
      {
        path: "products/create",
        name: "products.create",
        component: () => import("../views/products/CreateProduct.vue"),
      },
      {
        path: "orders",
        name: "orders.index",
        component: () => import("../views/orders/OrdersList.vue"),
      },
      {
        path: "orders/create",
        name: "orders.create",
        component: () => import("../views/orders/CreateOrder.vue"),
      },
      {
        path: "reports",
        name: "reports.index",
        component: () => import("../views/reports/ReportsView.vue"),
      },
      {
        path: "settings",
        name: "settings.index",
        component: () => import("../views/settings/SettingsView.vue"),
      },
    ],
  },
  { path: "/:pathMatch(.*)*", redirect: "/dashboard" },
];

const router = createRouter({ history: createWebHistory(), routes });
export default router;
