<template>
  <div>
    <a-modal v-model:open="open" :width="940" :title="state.title" @ok="onOk" @cancel="handleCancel">
      <div style="width: 85%;float: right">
        <a-flex justify="space-between" align="center">
          <a-input-search v-model:value="state.file_name" placeholder="请输入文件名" style="width: 40%;"
                          @search="fetchFileList"/>
          <a-space :size="15">
            <a-button danger type="dashed" @click="handleChunkUpload"> 大文件上传</a-button>
            <a-button type="primary" @click="handleUpload"> 普通上传</a-button>
          </a-space>
        </a-flex>
      </div>
      <div style="height: 650px;">
        <br>
        <a-tabs
            v-model:activeKey="state.activeKey"
            tab-position="left"
            :style="{ height: '100%', width: '100%'}"
            @change="fetchFileList"
        >
          <a-tab-pane v-for="group in groupList" :key="group.value" :tab="`${group.label}`">
            <a-card class="w-full mt-1 h-620px">
              <div class="file-list-body">
                <!-- 文件列表 -->
                <ul v-if="fileList && fileList.length" class="file-list-ul clearfix">
                  <li
                      class="file-item"
                      :class="{ active: state.selectedItems.indexOf(item) > -1 }"
                      v-for="(item, index) in fileList"
                      :key="index"
                      @click="onSelectItem(item)"
                  >
                    <div
                        v-if="item.file_type === FileTypeEnum.IMAGE"
                        class="img-cover"
                        :style="{ backgroundImage: `url('${domain+item.file_path}')`, width: '95px' }"
                    ></div>
                    <div
                        v-if="item.file_type !== FileTypeEnum.IMAGE"
                        class="img-cover"
                        :style="handleBackgroundStyle(item)"
                    ></div>
                    <div class="file-name oneline-hide">{{ item.file_name }}</div>
                    <div class="select-mask">
                      <CheckOutlined class="selected-icon" type="check"/>
                    </div>
                  </li>
                </ul>
                <!-- 无数据时显示 -->
                <a-empty v-else-if="!state.isLoading"/>
              </div>
              <!-- 底部操作栏 -->
              <div v-show="pagination.totalPages > 1" style="padding-top: 10px;">
                <a-pagination
                    v-model:current="pagination.currentPage"
                    :total="pagination.totalItems"
                    :defaultPageSize="24"
                    @change="fetchFileList"
                    :showSizeChanger="false"
                />
              </div>
            </a-card>
          </a-tab-pane>
        </a-tabs>
        <UploadModal ref="UploadModalRef" @upload-success="handleUploadSuccess"/>
        <ChunkModal ref="ChunkModalRef" @upload-success="handleUploadSuccess"/>
      </div>
    </a-modal>
  </div>
</template>

<script setup lang="ts">
import {reactive, ref} from "vue";
import * as GroupApi from '@/api/backend/uploadGroup.ts'
import * as FileApi from '@/api/backend/uploadFile.ts'
import {FileTypeEnum} from "@/enums/fileTypeEnum.ts";
import {CheckOutlined} from "@ant-design/icons-vue";
import {message} from "ant-design-vue/es/components";

defineOptions({
  name: 'FileModal',
});
const emit = defineEmits(['handleSubmit', 'handleCancel']);
const domain = import.meta.env.VITE_DOMAIN_URL;
const open = ref<boolean>(false);
const UploadModalRef = ref();
const ChunkModalRef = ref();
const state = reactive({
  activeKey: 0,
  file_name: '',
  fileType: FileTypeEnum.IMAGE,
  multiple: false,
  maxNum: 1,
  isLoading: false,
  title: '文件资源库',
  selectedItems: []
})
const pagination = ref({
  currentPage: 1,
  totalItems: 0,
  totalPages: 0
})
const fileList = ref([])
const groupList = ref([])

/** 打开文件资源库弹窗 */
const openFileModal = (type: FileTypeEnum, isMultiple: boolean, max: number, selected: number) => {
  if (type === FileTypeEnum.IMAGE) {
    state.title = '文件资源库（图片）'
  } else if (type === FileTypeEnum.VIDEO) {
    state.title = '文件资源库（视频）'
  } else {
    state.title = '文件资源库（文件）'
  }
  state.selectedItems = []
  state.fileType = type
  state.multiple = isMultiple
  state.maxNum = max
  state.selectedNum = selected
  fetchFileList()
  fetchGroupList()
  open.value = true
}

/** 打开上传文件弹窗 */
const handleUpload = () => {
  UploadModalRef.value.openUploadModal(state.fileType, state.activeKey)
}

/** 打开大文件上传弹窗 */
const handleChunkUpload = () => {
  ChunkModalRef.value.openChunkUploadModal(state.fileType, state.activeKey)
}

/** 获取分组列表 */
const fetchGroupList = async () => {
  groupList.value = await GroupApi.selectGroupList()
}

/** 获取当前类型的文件列表 */
const fetchFileList = async () => {
  state.isLoading = true
  const {meta, items} = await FileApi.list({
    page: pagination.value.currentPage,
    file_type: state.fileType,
    group_id: state.activeKey,
    file_name: state.file_name,
    pageSize: 24
  })
  pagination.value = meta
  console.log(pagination.value)
  fileList.value = items
  state.isLoading = false
}

/** 上传成功回调 */
const handleUploadSuccess = () => {
  pagination.value.currentPage = 1
  fetchFileList()
}

/** 点击文件列表项 */
const onSelectItem = function (item) {
  // 记录选中状态
  if (!state.multiple) {
    state.selectedItems = [item]
    return
  }
  const key = selectedItems.value.indexOf(item)
  const selected = key > -1
  // 验证数量限制
  if (!selected && (state.selectedItems.length + state.selectedNum) >= state.maxNum) {
    message.warning(`最多可选${state.maxNum}个文件`, 1)
    return
  }
  if (!selected) {
    state.selectedItems.push(item)
  } else {
    state.selectedItems.splice(key, 1)
  }
}

/** 获取背景样式 */
const handleBackgroundStyle = (item) => {
  let path = ''
  if (item.file_type === FileTypeEnum.VIDEO) {
    path = domain + '/file_icons/video/' + item.file_ext + '.png'
  } else if (item.file_type === FileTypeEnum.FILE) {
    path = domain + '/file_icons/file/' + item.file_ext + '.png'
  }
  return {
    backgroundImage: `url('${path}')`,
    width: '95px',
  };
}
const onOk = () => {
  emit('handleSubmit', state.selectedItems)
  open.value = false
}

const handleCancel = () => {
  emit('handleCancel')
}
// 暴露方法
defineExpose({
  openFileModal
})
</script>
<style scoped lang="less">
.fixed-width-tab {
  width: 200px;
}

.file-list-body {
  height: 550px;
  .file-list-ul {
    margin: 0;
    padding: 0;
    list-style-type: none;
  }

  .file-item {
    width: 110px;
    position: relative;
    cursor: pointer;
    border-radius: 2px;
    padding: 4px;
    border: 1px solid rgba(0, 0, 0, 0.05);
    float: left;
    margin: 4px;
    -webkit-transition: All 0.2s ease-in-out;
    -moz-transition: All 0.2s ease-in-out;
    -o-transition: All 0.2s ease-in-out;
    transition: All 0.2s ease-in-out;

    &:hover {
      border: 1px solid #16bce2;
    }
  }

  .file-item {
    // 文件名称
    .file-name {
      font-size: 12px;
      text-align: center;
      color: #3b4acc;
      margin-top: 5px;
    }

    // 预览图
    .img-cover {
      margin: 0 auto;
      width: 95px;
      height: 95px;
      background: no-repeat center center / 100%;
    }

    // 遮罩层(选中时)
    &.active .select-mask {
      display: block;
    }

    .select-mask {
      display: none;
      position: absolute;
      top: 0;
      bottom: 0;
      left: 0;
      right: 0;
      background: rgba(0, 0, 0, 0.45);
      text-align: center;
      border-radius: 2px;

      .selected-icon {
        font-size: 26px;
        color: #fff;
        text-align: center;
        position: absolute;
        top: 38%;
        left: 38%;
      }
    }
  }
}
</style>