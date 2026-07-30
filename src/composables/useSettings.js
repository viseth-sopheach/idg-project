import { ref } from "vue";
import api from "../services/api";
import { useAuthStore } from "../stores/auth";

export function useSettings() {
  const authStore = useAuthStore();

  const profileForm = ref({ name: "", email: "", current_password: "" });
  const profileSaving = ref(false);
  const profileError = ref("");
  const profileSuccess = ref("");

  const passwordForm = ref({
    current_password: "",
    password: "",
    password_confirmation: "",
  });
  const passwordSaving = ref(false);
  const passwordError = ref("");
  const passwordSuccess = ref("");

  const loadProfile = () => {
    profileForm.value = {
      name: authStore.user?.name ?? "",
      email: authStore.user?.email ?? "", // display only, never submitted
      current_password: "",
    };
  };

  const saveProfile = async () => {
    profileError.value = "";
    profileSuccess.value = "";
    profileSaving.value = true;
    try {
      const res = await api.put("/profile", {
        name: profileForm.value.name,
        current_password: profileForm.value.current_password,
      });
      authStore.user = { ...authStore.user, ...res.data?.data };
      profileForm.value.current_password = "";
      profileSuccess.value = "Profile updated successfully.";
    } catch (err) {
      const data = err.response?.data;
      profileError.value = data?.errors
        ? Object.values(data.errors).flat().join(" ")
        : data?.message || "Failed to update profile.";
      console.error(err);
    } finally {
      profileSaving.value = false;
    }
  };

  const savePassword = async () => {
    passwordError.value = "";
    passwordSuccess.value = "";

    if (
      passwordForm.value.password !== passwordForm.value.password_confirmation
    ) {
      passwordError.value = "New password confirmation does not match.";
      return;
    }

    passwordSaving.value = true;
    try {
      await api.put("/password", passwordForm.value);
      passwordSuccess.value = "Password updated successfully.";
      passwordForm.value = {
        current_password: "",
        password: "",
        password_confirmation: "",
      };
    } catch (err) {
      const data = err.response?.data;
      passwordError.value = data?.errors
        ? Object.values(data.errors).flat().join(" ")
        : data?.message || "Failed to update password.";
      console.error(err);
    } finally {
      passwordSaving.value = false;
    }
  };

  return {
    profileForm,
    profileSaving,
    profileError,
    profileSuccess,
    passwordForm,
    passwordSaving,
    passwordError,
    passwordSuccess,
    loadProfile,
    saveProfile,
    savePassword,
  };
}
