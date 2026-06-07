<template>
  <div class="logs-page">
    <div class="page-header">
      <h1>日志管理</h1>
      <el-dropdown @command="handleExport" trigger="click">
        <el-button type="primary">
          <el-icon><Download /></el-icon>导出日志
          <el-icon class="el-icon--right"><ArrowDown /></el-icon>
        </el-button>
        <template #dropdown>
          <el-dropdown-menu>
            <el-dropdown-item command="login">导出登录日志</el-dropdown-item>
            <el-dropdown-item command="grade">导出成绩修改日志</el-dropdown-item>
            <el-dropdown-item command="import">导出数据导入日志</el-dropdown-item>
          </el-dropdown-menu>
        </template>
      </el-dropdown>
    </div>

    <div class="card">
      <el-tabs v-model="activeTab" @tab-change="handleTabChange">
        <el-tab-pane label="登录日志" name="login">
          <div class="search-bar">
            <el-input v-model="loginSearch.keyword" placeholder="搜索用户名" clearable @keyup.enter="fetchLoginLogs">
              <template #prefix><el-icon><Search /></el-icon></template>
            </el-input>
            <el-select v-model="loginSearch.success" placeholder="登录状态" clearable @change="fetchLoginLogs">
              <el-option label="成功" :value="true" />
              <el-option label="失败" :value="false" />
            </el-select>
            <el-date-picker
              v-model="loginSearch.dateRange"
              type="daterange"
              range-separator="至"
              start-placeholder="开始日期"
              end-placeholder="结束日期"
              value-format="YYYY-MM-DD"
              @change="fetchLoginLogs"
            />
            <el-button type="primary" @click="fetchLoginLogs">搜索</el-button>
            <el-button @click="resetLoginSearch">重置</el-button>
          </div>

          <el-table :data="loginData" v-loading="loginLoading" stripe>
            <el-table-column prop="id" label="ID" width="80" />
            <el-table-column prop="username" label="用户名" width="120" />
            <el-table-column prop="login_time" label="登录时间" width="170" />
            <el-table-column prop="login_ip" label="登录IP" width="140" />
            <el-table-column prop="device_info" label="设备信息" min-width="200" show-overflow-tooltip />
            <el-table-column prop="status" label="登录状态" width="100">
              <template #default="{ row }">
                <el-tag :type="row.status === 1 ? 'success' : 'danger'" size="small">
                  {{ row.status === 1 ? '成功' : '失败' }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="failure_reason" label="失败原因" min-width="150" show-overflow-tooltip />
            <el-table-column label="操作" width="100" fixed="right">
              <template #default="{ row }">
                <el-button type="primary" link size="small" @click="showDetail('login', row)">详情</el-button>
              </template>
            </el-table-column>
          </el-table>

          <div class="pagination-wrapper">
            <el-pagination
              v-model:current-page="loginPagination.page"
              v-model:page-size="loginPagination.pageSize"
              :total="loginPagination.total"
              :page-sizes="[10, 20, 50, 100]"
              layout="total, sizes, prev, pager, next, jumper"
              @size-change="fetchLoginLogs"
              @current-change="fetchLoginLogs"
            />
          </div>
        </el-tab-pane>

        <el-tab-pane label="成绩修改日志" name="grade">
          <div class="search-bar">
            <el-input v-model="gradeSearch.keyword" placeholder="搜索操作用户" clearable @keyup.enter="fetchGradeLogs">
              <template #prefix><el-icon><Search /></el-icon></template>
            </el-input>
            <el-select v-model="gradeSearch.operationType" placeholder="操作类型" clearable @change="fetchGradeLogs">
              <el-option label="创建" value="create" />
              <el-option label="修改" value="update" />
              <el-option label="删除" value="delete" />
            </el-select>
            <el-date-picker
              v-model="gradeSearch.dateRange"
              type="daterange"
              range-separator="至"
              start-placeholder="开始日期"
              end-placeholder="结束日期"
              value-format="YYYY-MM-DD"
              @change="fetchGradeLogs"
            />
            <el-button type="primary" @click="fetchGradeLogs">搜索</el-button>
            <el-button @click="resetGradeSearch">重置</el-button>
          </div>

          <el-table :data="gradeData" v-loading="gradeLoading" stripe>
            <el-table-column prop="id" label="ID" width="80" />
            <el-table-column prop="operator_username" label="操作用户" width="120" />
            <el-table-column prop="operation_time" label="操作时间" width="170" />
            <el-table-column prop="student_id" label="学生ID" width="90" />
            <el-table-column prop="course_id" label="课程ID" width="90" />
            <el-table-column label="成绩变更" width="150">
              <template #default="{ row }">
                <div class="score-change">
                  <span class="old-score">{{ row.old_score ?? '-' }}</span>
                  <el-icon><ArrowRight /></el-icon>
                  <span class="new-score">{{ row.new_score }}</span>
                </div>
              </template>
            </el-table-column>
            <el-table-column label="等级变更" width="150">
              <template #default="{ row }">
                <div class="score-change">
                  <span class="old-grade">{{ row.old_grade_level ?? '-' }}</span>
                  <el-icon><ArrowRight /></el-icon>
                  <span class="new-grade">{{ row.new_grade_level ?? '-' }}</span>
                </div>
              </template>
            </el-table-column>
            <el-table-column prop="semester" label="学期" width="130" />
            <el-table-column prop="exam_type" label="考试类型" width="90" />
            <el-table-column prop="operation_type" label="操作类型" width="90">
              <template #default="{ row }">
                <el-tag :type="getOperationType(row.operation_type)" size="small">
                  {{ getOperationTypeText(row.operation_type) }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column label="操作" width="100" fixed="right">
              <template #default="{ row }">
                <el-button type="primary" link size="small" @click="showDetail('grade', row)">详情</el-button>
              </template>
            </el-table-column>
          </el-table>

          <div class="pagination-wrapper">
            <el-pagination
              v-model:current-page="gradePagination.page"
              v-model:page-size="gradePagination.pageSize"
              :total="gradePagination.total"
              :page-sizes="[10, 20, 50, 100]"
              layout="total, sizes, prev, pager, next, jumper"
              @size-change="fetchGradeLogs"
              @current-change="fetchGradeLogs"
            />
          </div>
        </el-tab-pane>

        <el-tab-pane label="数据导入日志" name="import">
          <div class="search-bar">
            <el-input v-model="importSearch.keyword" placeholder="搜索操作用户" clearable @keyup.enter="fetchImportLogs">
              <template #prefix><el-icon><Search /></el-icon></template>
            </el-input>
            <el-select v-model="importSearch.dataType" placeholder="数据类型" clearable @change="fetchImportLogs">
              <el-option label="学生数据" value="student" />
              <el-option label="成绩数据" value="grade" />
            </el-select>
            <el-select v-model="importSearch.importMethod" placeholder="导入方式" clearable @change="fetchImportLogs">
              <el-option label="文件导入" value="file" />
              <el-option label="粘贴导入" value="paste" />
            </el-select>
            <el-date-picker
              v-model="importSearch.dateRange"
              type="daterange"
              range-separator="至"
              start-placeholder="开始日期"
              end-placeholder="结束日期"
              value-format="YYYY-MM-DD"
              @change="fetchImportLogs"
            />
            <el-button type="primary" @click="fetchImportLogs">搜索</el-button>
            <el-button @click="resetImportSearch">重置</el-button>
          </div>

          <el-table :data="importData" v-loading="importLoading" stripe>
            <el-table-column prop="id" label="ID" width="80" />
            <el-table-column prop="operator_username" label="操作用户" width="120" />
            <el-table-column prop="operation_time" label="操作时间" width="170" />
            <el-table-column prop="data_type" label="数据类型" width="100">
              <template #default="{ row }">
                <el-tag :type="row.data_type === 'student' ? 'primary' : 'success'" size="small">
                  {{ row.data_type === 'student' ? '学生数据' : '成绩数据' }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="import_method" label="导入方式" width="100">
              <template #default="{ row }">
                <el-tag :type="row.import_method === 'file' ? 'info' : 'warning'" size="small">
                  {{ row.import_method === 'file' ? '文件导入' : '粘贴导入' }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="file_name" label="文件名" min-width="180" show-overflow-tooltip />
            <el-table-column prop="total_count" label="总记录数" width="100" align="center" />
            <el-table-column prop="success_count" label="成功数" width="90" align="center">
              <template #default="{ row }">
                <span class="text-success">{{ row.success_count }}</span>
              </template>
            </el-table-column>
            <el-table-column prop="failed_count" label="失败数" width="90" align="center">
              <template #default="{ row }">
                <span v-if="row.failed_count > 0" class="text-danger">{{ row.failed_count }}</span>
                <span v-else>0</span>
              </template>
            </el-table-column>
            <el-table-column label="操作" width="100" fixed="right">
              <template #default="{ row }">
                <el-button type="primary" link size="small" @click="showDetail('import', row)">详情</el-button>
              </template>
            </el-table-column>
          </el-table>

          <div class="pagination-wrapper">
            <el-pagination
              v-model:current-page="importPagination.page"
              v-model:page-size="importPagination.pageSize"
              :total="importPagination.total"
              :page-sizes="[10, 20, 50, 100]"
              layout="total, sizes, prev, pager, next, jumper"
              @size-change="fetchImportLogs"
              @current-change="fetchImportLogs"
            />
          </div>
        </el-tab-pane>
      </el-tabs>
    </div>

    <el-dialog v-model="detailVisible" :title="detailTitle" width="600px" destroy-on-close>
      <el-descriptions :column="1" border v-if="currentDetail">
        <template v-if="activeTab === 'login'">
          <el-descriptions-item label="ID">{{ currentDetail.id }}</el-descriptions-item>
          <el-descriptions-item label="用户名">{{ currentDetail.username }}</el-descriptions-item>
          <el-descriptions-item label="登录时间">{{ currentDetail.login_time }}</el-descriptions-item>
          <el-descriptions-item label="登录IP">{{ currentDetail.login_ip }}</el-descriptions-item>
          <el-descriptions-item label="设备信息">{{ currentDetail.device_info }}</el-descriptions-item>
          <el-descriptions-item label="登录状态">
            <el-tag :type="currentDetail.status === 1 ? 'success' : 'danger'" size="small">
              {{ currentDetail.status === 1 ? '成功' : '失败' }}
            </el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="失败原因" v-if="currentDetail.status === 0">
            {{ currentDetail.failure_reason || '-' }}
          </el-descriptions-item>
        </template>

        <template v-if="activeTab === 'grade'">
          <el-descriptions-item label="ID">{{ currentDetail.id }}</el-descriptions-item>
          <el-descriptions-item label="操作用户">{{ currentDetail.operator_username }}</el-descriptions-item>
          <el-descriptions-item label="操作时间">{{ currentDetail.operation_time }}</el-descriptions-item>
          <el-descriptions-item label="学生ID">{{ currentDetail.student_id }}</el-descriptions-item>
          <el-descriptions-item label="课程ID">{{ currentDetail.course_id }}</el-descriptions-item>
          <el-descriptions-item label="修改前成绩">{{ currentDetail.old_score ?? '-' }}</el-descriptions-item>
          <el-descriptions-item label="修改后成绩">{{ currentDetail.new_score }}</el-descriptions-item>
          <el-descriptions-item label="修改前等级">{{ currentDetail.old_grade_level ?? '-' }}</el-descriptions-item>
          <el-descriptions-item label="修改后等级">{{ currentDetail.new_grade_level ?? '-' }}</el-descriptions-item>
          <el-descriptions-item label="学期">{{ currentDetail.semester }}</el-descriptions-item>
          <el-descriptions-item label="考试类型">{{ currentDetail.exam_type }}</el-descriptions-item>
          <el-descriptions-item label="操作类型">
            <el-tag :type="getOperationType(currentDetail.operation_type)" size="small">
              {{ getOperationTypeText(currentDetail.operation_type) }}
            </el-tag>
          </el-descriptions-item>
        </template>

        <template v-if="activeTab === 'import'">
          <el-descriptions-item label="ID">{{ currentDetail.id }}</el-descriptions-item>
          <el-descriptions-item label="操作用户">{{ currentDetail.operator_username }}</el-descriptions-item>
          <el-descriptions-item label="操作时间">{{ currentDetail.operation_time }}</el-descriptions-item>
          <el-descriptions-item label="数据类型">
            <el-tag :type="currentDetail.data_type === 'student' ? 'primary' : 'success'" size="small">
              {{ currentDetail.data_type === 'student' ? '学生数据' : '成绩数据' }}
            </el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="导入方式">
            <el-tag :type="currentDetail.import_method === 'file' ? 'info' : 'warning'" size="small">
              {{ currentDetail.import_method === 'file' ? '文件导入' : '粘贴导入' }}
            </el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="文件名">{{ currentDetail.file_name || '-' }}</el-descriptions-item>
          <el-descriptions-item label="总记录数">{{ currentDetail.total_count }}</el-descriptions-item>
          <el-descriptions-item label="成功数">
            <span class="text-success">{{ currentDetail.success_count }}</span>
          </el-descriptions-item>
          <el-descriptions-item label="失败数">
            <span v-if="currentDetail.failed_count > 0" class="text-danger">{{ currentDetail.failed_count }}</span>
            <span v-else>0</span>
          </el-descriptions-item>
          <el-descriptions-item label="失败详情" v-if="currentDetail.failure_details && currentDetail.failure_details.length > 0">
            <div class="failure-details">
              <div v-for="(error, index) in currentDetail.failure_details" :key="index" class="error-item">
                {{ error }}
              </div>
            </div>
          </el-descriptions-item>
        </template>
      </el-descriptions>
      <template #footer>
        <el-button @click="detailVisible = false">关闭</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { logApi } from '@/api'
import { showSuccess } from '@/utils/request'

const activeTab = ref('login')
const detailVisible = ref(false)
const currentDetail = ref(null)

const loginLoading = ref(false)
const gradeLoading = ref(false)
const importLoading = ref(false)

const loginData = ref([])
const gradeData = ref([])
const importData = ref([])

const loginSearch = reactive({ keyword: '', success: null, dateRange: [] })
const gradeSearch = reactive({ keyword: '', operationType: null, dateRange: [] })
const importSearch = reactive({ keyword: '', dataType: null, importMethod: null, dateRange: [] })

const loginPagination = reactive({ page: 1, pageSize: 20, total: 0 })
const gradePagination = reactive({ page: 1, pageSize: 20, total: 0 })
const importPagination = reactive({ page: 1, pageSize: 20, total: 0 })

const detailTitle = computed(() => {
  if (activeTab.value === 'login') return '登录日志详情'
  if (activeTab.value === 'grade') return '成绩修改日志详情'
  return '数据导入日志详情'
})

function getOperationType(type) {
  if (type === 'create') return 'success'
  if (type === 'update') return 'primary'
  return 'danger'
}

function getOperationTypeText(type) {
  if (type === 'create') return '创建'
  if (type === 'update') return '修改'
  return '删除'
}

function getSearchParams(search, pagination, type) {
  const params = { page: pagination.page, pageSize: pagination.pageSize }
  
  if (search.keyword) {
    if (type === 'login') params.username = search.keyword
    else params.operator = search.keyword
  }
  
  if (type === 'login' && search.success !== null) {
    params.success = search.success ? 1 : 0
  }
  if (type === 'grade' && search.operationType) {
    params.operationType = search.operationType
  }
  if (type === 'import') {
    if (search.dataType) params.dataType = search.dataType
    if (search.importMethod) params.importMethod = search.importMethod
  }
  
  if (search.dateRange && search.dateRange.length === 2) {
    params.startTime = search.dateRange[0]
    params.endTime = search.dateRange[1]
  }
  
  return params
}

async function fetchLoginLogs() {
  loginLoading.value = true
  try {
    const params = getSearchParams(loginSearch, loginPagination, 'login')
    const res = await logApi.getLoginLogs(params)
    loginData.value = res.data.items
    loginPagination.total = res.data.total
  } finally {
    loginLoading.value = false
  }
}

async function fetchGradeLogs() {
  gradeLoading.value = true
  try {
    const params = getSearchParams(gradeSearch, gradePagination, 'grade')
    const res = await logApi.getGradeChangeLogs(params)
    gradeData.value = res.data.items
    gradePagination.total = res.data.total
  } finally {
    gradeLoading.value = false
  }
}

async function fetchImportLogs() {
  importLoading.value = true
  try {
    const params = getSearchParams(importSearch, importPagination, 'import')
    const res = await logApi.getDataImportLogs(params)
    importData.value = res.data.items
    importPagination.total = res.data.total
  } finally {
    importLoading.value = false
  }
}

function resetLoginSearch() {
  Object.assign(loginSearch, { keyword: '', success: null, dateRange: [] })
  loginPagination.page = 1
  fetchLoginLogs()
}

function resetGradeSearch() {
  Object.assign(gradeSearch, { keyword: '', operationType: null, dateRange: [] })
  gradePagination.page = 1
  fetchGradeLogs()
}

function resetImportSearch() {
  Object.assign(importSearch, { keyword: '', dataType: null, importMethod: null, dateRange: [] })
  importPagination.page = 1
  fetchImportLogs()
}

function handleTabChange(tab) {
  if (tab === 'login' && loginData.value.length === 0) fetchLoginLogs()
  if (tab === 'grade' && gradeData.value.length === 0) fetchGradeLogs()
  if (tab === 'import' && importData.value.length === 0) fetchImportLogs()
}

function showDetail(type, row) {
  currentDetail.value = { ...row }
  detailVisible.value = true
}

function handleExport(type) {
  let exportUrl = ''
  const searchMap = {
    login: { search: loginSearch, exportFn: logApi.exportLoginLogs },
    grade: { search: gradeSearch, exportFn: logApi.exportGradeChangeLogs },
    import: { search: importSearch, exportFn: logApi.exportDataImportLogs }
  }
  
  const config = searchMap[type]
  if (!config) return
  
  const params = getSearchParams(config.search, { page: 1, pageSize: 10000 }, type)
  exportUrl = config.exportFn(params)
  
  window.open(exportUrl, '_blank')
  showSuccess('导出任务已开始')
}

onMounted(() => {
  fetchLoginLogs()
})
</script>

<style lang="scss" scoped>
.logs-page {
  .page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;

    h1 {
      font-size: 24px;
      font-weight: 600;
      color: #303133;
      margin: 0;
    }
  }

  .card {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);

    :deep(.el-tabs__header) {
      margin: 0 0 20px 0;
    }
  }

  .search-bar {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;

    .el-input {
      width: 200px;
    }

    .el-select {
      width: 150px;
    }
  }

  .pagination-wrapper {
    display: flex;
    justify-content: flex-end;
    margin-top: 20px;
  }

  .score-change {
    display: flex;
    align-items: center;
    gap: 6px;

    .old-score,
    .old-grade {
      color: #909399;
      text-decoration: line-through;
    }

    .new-score,
    .new-grade {
      color: #409eff;
      font-weight: 500;
    }
  }

  .text-success {
    color: #67c23a;
    font-weight: 500;
  }

  .text-danger {
    color: #f56c6c;
    font-weight: 500;
  }

  .failure-details {
    max-height: 200px;
    overflow-y: auto;

    .error-item {
      padding: 4px 0;
      color: #f56c6c;
      font-size: 13px;
      border-bottom: 1px solid #f0f0f0;

      &:last-child {
        border-bottom: none;
      }
    }
  }
}
</style>
