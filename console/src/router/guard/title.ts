import { useTitle } from '@vueuse/core';
import type { Router } from 'vue-router';

export function createDocumentTitleGuard(router: Router) {
  router.afterEach(to => {
    const { title } = to.meta;

    useTitle(title);
  });
}
