<template>
  <Layout.Header :style="headerStyle" class="layout-header">
    <div class="header-left">
      <slot name="left">
        <Space :size="20">
          <span
              class="menu-fold cursor-pointer"
              @click="() => emit('update:collapsed', !collapsed)"
          >
            <component :is="collapsed ? MenuUnfoldOutlined : MenuFoldOutlined"/>
          </span>
          <LayoutBreadcrumb/>
        </Space>
      </slot>
    </div>
    <div class="header-menu">
      <slot name="menu"/>
    </div>
    <div class="header-right">
      <Space :size="20">
        <FullScreen/>
        <Dropdown placement="bottomRight">
          <Avatar :alt="userInfo.username">{{ userInfo.username }}</Avatar>
          <template #overlay>
            <Menu>
              <Menu.Item @click="$router.push({ name: 'account-settings' })">
                <setting-outlined/>
                个人设置
              </Menu.Item>
              <Menu.Divider/>
              <Menu.Item>
                <div @click.prevent="doLogout">
                  <poweroff-outlined/>
                  退出系统
                </div>
              </Menu.Item>
            </Menu>
          </template>
        </Dropdown>
        <ProjectSetting/>
      </Space>
    </div>
  </Layout.Header>
</template>

<script lang="tsx" setup>
import {computed, type CSSProperties} from 'vue';
import {useRouter, useRoute, createRouter as $router} from 'vue-router';
import {
  QuestionCircleOutlined,
  MenuFoldOutlined,
  MenuUnfoldOutlined,
  PoweroffOutlined,
  SettingOutlined
} from '@ant-design/icons-vue';
import {
  Layout,
  message,
  Modal,
  Dropdown,
  Menu,
  Space,
  Avatar,
  type MenuTheme,
} from 'ant-design-vue';
import {FullScreen, ProjectSetting, LayoutBreadcrumb} from './components/';
import {useUserStore} from '@/store/modules/user';
import {useKeepAliveStore} from '@/store/modules/keepAlive';
import {LOGIN_NAME} from '@/router/constant';
import {useLayoutSettingStore} from '@/store/modules/layoutSetting';


defineProps({
  collapsed: {
    type: Boolean,
  },
  theme: {
    type: String as () => MenuTheme,
  },
});
const emit = defineEmits(['update:collapsed']);
const userStore = useUserStore();
const layoutSettingStore = useLayoutSettingStore();
const keepAliveStore = useKeepAliveStore();

const router = useRouter();
const route = useRoute();
const userInfo = computed(() => userStore.userInfo);
const headerStyle = computed<CSSProperties>(() => {
  const {navTheme, layout} = layoutSettingStore.layoutSetting;
  const isDark = navTheme === 'dark' && layout === 'topmenu';
  return {
    backgroundColor: navTheme === 'realDark' || isDark ? '' : 'rgba(255, 255, 255, 0.85)',
    color: isDark ? 'rgba(255, 255, 255, 0.85)' : '',
  };
});

// 退出登录
const doLogout = () => {
  Modal.confirm({
    title: '您确定要退出登录吗？',
    icon: <QuestionCircleOutlined/>,
    centered: true,
    onOk: async () => {
      await userStore.logout();
      keepAliveStore.clear();
      // 移除标签页
      localStorage.clear();
      message.success('退出登录');
      router.replace({
        name: LOGIN_NAME,
        query: {
          redirect: route.fullPath,
        },
      });
    },
  });
};
</script>

<style lang="less" scoped>
.layout-header {
  display: flex;
  position: sticky;
  z-index: 10;
  top: 0;
  align-items: center;
  justify-content: space-between;
  height: var(--app-header-height);
  padding: 0 20px;

  .header-right {
    min-width: 105px;
    cursor: pointer;
  }

  .header-menu {
    flex: 1;
    align-items: center;
    min-width: 0;
  }
}
</style>
