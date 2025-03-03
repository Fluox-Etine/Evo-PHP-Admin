/**
 * example
 * path -> ./components/user
 * <a-button v-if="$auth('user.add')">Button</a-button>
 * path -> ./components/sys/user
 * <a-button v-if="$auth('sysUser.add')">Button</a-button>
 */

import type { PermissionType } from './permCode';
import type { App, Directive, DirectiveBinding } from 'vue';
import { useUserStore } from '@/store/modules/user';

/**
 * 验证权限
 * @returns {boolean} true | false
 * @param permCode
 */
export const hasPermission = (permCode: PermissionType) => {
  const permissionList = useUserStore().perms;

  return permissionList.some((n) => n === permCode);
};

const vAuth: Directive = {
  mounted(el: Element, binding: DirectiveBinding<any>) {
    const bindVal = binding.value;

    if (bindVal == undefined) return;

    if (!hasPermission(bindVal)) {
      el.remove();
    }
  },
};

export default {
  install(app: App) {
    app.config.globalProperties.$auth = hasPermission;
    app.directive('auth', vAuth);
  },
};
