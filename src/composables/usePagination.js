import { computed } from "vue";

/**
 * @param {import('vue').Ref<{ current_page: number, last_page: number }>} meta
 * @param {number} windowSize
 */
export function usePagination(meta, windowSize = 5) {
  const pageNumbers = computed(() => {
    const total = meta.value.last_page;
    const current = meta.value.current_page;
    let start = Math.max(1, current - Math.floor(windowSize / 2));
    let end = Math.min(total, start + windowSize - 1);
    start = Math.max(1, end - windowSize + 1);

    const pages = [];
    for (let p = start; p <= end; p++) pages.push(p);
    return pages;
  });

  return { pageNumbers };
}
