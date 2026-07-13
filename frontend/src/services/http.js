import axios from 'axios'

const http = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  headers: {
    Accept: 'application/json',
  },
})

// Interceptor de requisição: preenchido na Entrega 1 com o token de autenticação.
http.interceptors.request.use((config) => config)

// Interceptor de resposta: preenchido na Entrega 1 com o tratamento de erro 401.
http.interceptors.response.use(
  (response) => response,
  (error) => Promise.reject(error),
)

export default http
