import { defineStore } from "pinia";
import api from "../services/api";

export const useAuthStore = defineStore("auth", {
  state: () => ({
    user: null,
    isAuthenticated: false,
  }),
  actions: {
    async login(credentials) {
      const response = await api.post("/login", credentials);
      const token = response.data?.data?.token;
      if (token) {
        localStorage.setItem("auth_token", token);
      }
      await this.fetchUser();
      return response;
    },
    async fetchUser() {
      try {
        const response = await api.get("/me");
        this.user = response.data?.data ?? null;
        this.isAuthenticated = !!this.user;
      } catch (error) {
        this.user = null;
        this.isAuthenticated = false;
      }
    },
    async logout() {
      try {
        await api.post("/logout");
      } finally {
        localStorage.removeItem("auth_token");
        this.user = null;
        this.isAuthenticated = false;
      }
    },
    async forgotPassword(email) {
      return api.post("/forgot-password", { email });
    },
    async resetPassword(payload) {
      return api.post("/reset-password", payload);
    },
  },
});
