import { request } from '@/utils/request'

export const studentApi = {
  getList(params) {
    return request.get('/students', { params })
  },
  
  getById(id) {
    return request.get(`/students/${id}`)
  },
  
  create(data) {
    return request.post('/students', data)
  },
  
  update(id, data) {
    return request.put(`/students/${id}`, data)
  },
  
  delete(id) {
    return request.delete(`/students/${id}`)
  },
  
  import(formData) {
    return request.post('/students/import', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
  },
  
  pasteImport(data) {
    return request.post('/students/paste-import', data)
  },
  
  export(params) {
    return `/api/students/export?token=${localStorage.getItem('token')}${params?.classId ? `&classId=${params.classId}` : ''}`
  },
  
  getTemplate() {
    return `/api/students/template?token=${localStorage.getItem('token')}`
  }
}

export const classApi = {
  getList(params) {
    return request.get('/classes', { params })
  },
  
  getAll() {
    return request.get('/classes/all')
  },
  
  getById(id) {
    return request.get(`/classes/${id}`)
  },
  
  create(data) {
    return request.post('/classes', data)
  },
  
  update(id, data) {
    return request.put(`/classes/${id}`, data)
  },
  
  delete(id) {
    return request.delete(`/classes/${id}`)
  },
  
  getStudents(id) {
    return request.get(`/classes/${id}/students`)
  }
}

export const courseApi = {
  getList(params) {
    return request.get('/courses', { params })
  },
  
  getAll() {
    return request.get('/courses/all')
  },
  
  getById(id) {
    return request.get(`/courses/${id}`)
  },
  
  create(data) {
    return request.post('/courses', data)
  },
  
  update(id, data) {
    return request.put(`/courses/${id}`, data)
  },
  
  delete(id) {
    return request.delete(`/courses/${id}`)
  }
}

export const gradeApi = {
  getList(params) {
    return request.get('/grades', { params })
  },
  
  getById(id) {
    return request.get(`/grades/${id}`)
  },
  
  create(data) {
    return request.post('/grades', data)
  },
  
  update(id, data) {
    return request.put(`/grades/${id}`, data)
  },
  
  delete(id) {
    return request.delete(`/grades/${id}`)
  },
  
  getStudentGrades(studentId, params) {
    return request.get(`/grades/student/${studentId}`, { params })
  },
  
  import(formData) {
    return request.post('/grades/import', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
  },
  
  pasteImport(data) {
    return request.post('/grades/paste-import', data)
  },
  
  export(params) {
    let url = `/api/grades/export?token=${localStorage.getItem('token')}`
    if (params?.courseId) url += `&courseId=${params.courseId}`
    if (params?.semester) url += `&semester=${params.semester}`
    return url
  }
}

export const statisticsApi = {
  getOverview() {
    return request.get('/statistics/overview')
  },
  
  getClassStats(classId, params) {
    return request.get(`/statistics/class/${classId}`, { params })
  },
  
  getCourseStats(courseId, params) {
    return request.get(`/statistics/course/${courseId}`, { params })
  },
  
  getStudentStats(studentId) {
    return request.get(`/statistics/student/${studentId}`)
  },
  
  getRanking(params) {
    return request.get('/statistics/ranking', { params })
  },
  
  getDistribution(params) {
    return request.get('/statistics/distribution', { params })
  },
  
  getTrend(params) {
    return request.get('/statistics/trend', { params })
  }
}

export const userApi = {
  getList(params) {
    return request.get('/users', { params })
  },
  
  getById(id) {
    return request.get(`/users/${id}`)
  },
  
  create(data) {
    return request.post('/users', data)
  },
  
  update(id, data) {
    return request.put(`/users/${id}`, data)
  },
  
  delete(id) {
    return request.delete(`/users/${id}`)
  }
}

export const logApi = {
  getLoginLogs(params) {
    return request.get('/logs/login', { params })
  },
  
  getGradeChangeLogs(params) {
    return request.get('/logs/grade-change', { params })
  },
  
  getDataImportLogs(params) {
    return request.get('/logs/data-import', { params })
  },
  
  exportLoginLogs(params) {
    let url = `/api/logs/login/export?token=${localStorage.getItem('token')}`
    if (params?.username) url += `&username=${encodeURIComponent(params.username)}`
    if (params?.success !== undefined && params.success !== null) url += `&success=${params.success ? 1 : 0}`
    if (params?.startTime) url += `&startTime=${encodeURIComponent(params.startTime)}`
    if (params?.endTime) url += `&endTime=${encodeURIComponent(params.endTime)}`
    return url
  },
  
  exportGradeChangeLogs(params) {
    let url = `/api/logs/grade-change/export?token=${localStorage.getItem('token')}`
    if (params?.operator) url += `&operator=${encodeURIComponent(params.operator)}`
    if (params?.studentId) url += `&studentId=${params.studentId}`
    if (params?.courseId) url += `&courseId=${params.courseId}`
    if (params?.operationType) url += `&operationType=${encodeURIComponent(params.operationType)}`
    if (params?.startTime) url += `&startTime=${encodeURIComponent(params.startTime)}`
    if (params?.endTime) url += `&endTime=${encodeURIComponent(params.endTime)}`
    return url
  },
  
  exportDataImportLogs(params) {
    let url = `/api/logs/data-import/export?token=${localStorage.getItem('token')}`
    if (params?.operator) url += `&operator=${encodeURIComponent(params.operator)}`
    if (params?.dataType) url += `&dataType=${encodeURIComponent(params.dataType)}`
    if (params?.importMethod) url += `&importMethod=${encodeURIComponent(params.importMethod)}`
    if (params?.startTime) url += `&startTime=${encodeURIComponent(params.startTime)}`
    if (params?.endTime) url += `&endTime=${encodeURIComponent(params.endTime)}`
    return url
  }
}
