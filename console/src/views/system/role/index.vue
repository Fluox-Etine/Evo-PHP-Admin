<template>
  <DynamicTable
      row-key="id"
      header-title="角色管理"
      title-tooltip="超级管理员默认拥有所有资源访问权限且不支持修改"
      :data-request="Api.roleList"
      :columns="columns"
      bordered
      size="small"
  >
    <template #toolbar>
      <a-button type="primary" :disabled="!$auth('system:role:create')" @click="openMenuModal({})">
        新增
      </a-button>
    </template>
  </DynamicTable>
</template>

<script lang="ts" setup>
import {baseColumns, type TableListItem, type TableColumnItem} from './columns';
import {roleSchemas} from './formSchemas';
import {useTable} from '@/components/business/dynamic-table';
import {useFormModal} from '@/hooks/useModal';
import * as Api from '@/api/backend/role';
import {menuList} from '@/api/backend/menu'

defineOptions({
  name: 'SystemPermissionRole',
});

const [DynamicTable, dynamicTableInstance] = useTable({
  pagination: false,
  size: 'small',
  rowKey: 'id',
  bordered: true,
  autoHeight: true,
});

const [showModal] = useFormModal();

const getCheckedKeys = (checkedList: number[], menus: API.MenuItemInfo[], total = []) => {
  return menus.reduce<number[]>((prev, curr) => {
    if (curr.children?.length) {
      getCheckedKeys(checkedList, curr.children, total);
    } else {
      if (checkedList.includes(curr.id)) {
        prev.push(curr.id);
      }
    }
    return prev;
  }, total);
};

/**
 * @description 打开新增/编辑弹窗
 */
const openMenuModal = async (record: Partial<TableListItem>) => {
  const [formRef] = await showModal({
    modalProps: {
      title: `${record.id ? '编辑' : '新增'}角色`,
      width: '50%',
      onFinish: async (values) => {
        record.id && (values.id = record.id);
        const menusRef = formRef?.compRefMap.get('menuIds')!;
        const params = {
          ...values,
          menuIds: [...menusRef.halfCheckedKeys, ...menusRef.checkedKeys],
        };
        if (record.id) {
          await Api.roleUpdate(params);
        } else {
          await Api.roleCreate(params);
        }

        dynamicTableInstance?.reload();
      },
    },
    formProps: {
      labelWidth: 100,
      schemas: roleSchemas,
    },
  });

  const menuTreeData = await menuList({});

  formRef?.updateSchema([
    {
      field: 'menuIds',
      componentProps: {treeData: menuTreeData},
    },
  ]);
  // 如果是编辑的话，需要获取角色详情
  if (record.id) {
    const roleInfo = await Api.roleInfo({id: record.id});

    formRef?.setFieldsValue({
      ...record,
      menuIds: getCheckedKeys(roleInfo.menuIds, menuTreeData),
    });
  }
};
const delRowConfirm = async (record: TableListItem) => {
  await Api.roleDelete({id: record.id});
  dynamicTableInstance?.reload();
};

const columns: TableColumnItem[] = [
  ...baseColumns,
  {
    title: '操作',
    width: 130,
    dataIndex: 'ACTION',
    hideInSearch: true,
    fixed: 'right',
    actions: ({record}) => [
      {
        label: '编辑',
        auth: {
          perm: 'system:role:update',
          effect: 'disable',
        },
        onClick: () => {
          openMenuModal(record);
        },
      },
      {
        label: '删除',
        auth: 'system:role:delete',
        popConfirm: {
          title: '你确定要删除吗？',
          placement: 'left',
          onConfirm: () => delRowConfirm(record),
        },
      },
    ],
  },
];
</script>
