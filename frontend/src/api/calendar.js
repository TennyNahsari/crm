import api from './axios'

export const getCalendarEvents = (params) => api.get('/calendar/events', { params })
