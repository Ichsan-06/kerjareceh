import router from '@/plugins/router'
import axios from 'axios'

const axiosIns = axios.create({
// You can add your headers here
// ================================
// baseURL: 'https://some-domain.com/api/',
// timeout: 1000,
// headers: {'X-Custom-Header': 'foobar'}
})


// ℹ️ Add request interceptor to send the authorization header on each subsequent request after login
// ℹ️ Add request interceptor to send the authorization header on each subsequent request after login
axiosIns.interceptors.request.use(config => {
  const token = localStorage.getItem('token')

  // If token is found
  if (token) {
    // Get request headers and if headers is undefined assign blank object
    config.headers = config.headers || {}

    // Set authorization header
    // ℹ️ JSON.parse will convert null to null
    config.headers.Authorization = token ? `Bearer ${token}` : ''
  }

  // Return modified config
  return config
})

// ℹ️ Add response interceptor to handle 401 response
axiosIns.interceptors.response.use(response => {
  return response
}, error => {
  // Handle error
  if (error.response.status === 401) {
    // ℹ️ Logout user and redirect to login page
    // Remove "accessToken" from localStorage
    localStorage.removeItem('token')
    localStorage.removeItem('user')

    // If you are using v-router, use simple replacement logic
    router.push('/login')
  }
  else {
    return Promise.reject(error)
  }
})

export default axiosIns
