import type { ApiErrorBody, ApiSuccess, AuthPayload, Todo, User } from '../types'

const API_URL = import.meta.env.VITE_API_URL ?? '/api/v1'

export class ApiError extends Error {
  status: number
  errors?: Record<string, string>

  constructor(message: string, status: number, errors?: Record<string, string>) {
    super(message)
    this.name = 'ApiError'
    this.status = status
    this.errors = errors
  }
}

async function request<T>(
  path: string,
  options: RequestInit = {},
  auth = true,
): Promise<ApiSuccess<T>> {
  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
    Accept: 'application/json',
    ...(options.headers as Record<string, string>),
  }

  if (auth) {
    const token = localStorage.getItem('token')
    if (token) {
      headers.Authorization = `Bearer ${token}`
    }
  }

  const response = await fetch(`${API_URL}${path}`, {
    ...options,
    headers,
  })

  let body: ApiSuccess<T> | ApiErrorBody
  try {
    body = await response.json()
  } catch {
    throw new ApiError('Invalid server response', response.status)
  }

  if (!body.success) {
    throw new ApiError(body.message, response.status, body.errors)
  }

  return body
}

export const api = {
  register(name: string, email: string, password: string) {
    return request<AuthPayload>('/auth/register', {
      method: 'POST',
      body: JSON.stringify({ name, email, password }),
    }, false)
  },

  login(email: string, password: string) {
    return request<AuthPayload>('/auth/login', {
      method: 'POST',
      body: JSON.stringify({ email, password }),
    }, false)
  },

  logout() {
    return request('/auth/logout', { method: 'POST' })
  },

  me() {
    return request<{ user: User }>('/auth/me')
  },

  getTodos(completed?: '0' | '1') {
    const query = completed !== undefined ? `?completed=${completed}` : ''
    return request<{ todos: Todo[] }>(`/todos${query}`)
  },

  createTodo(title: string, description?: string) {
    return request<{ todo: Todo }>('/todos', {
      method: 'POST',
      body: JSON.stringify({ title, description: description || null }),
    })
  },

  updateTodo(id: number, data: Partial<{ title: string; description: string; completed: boolean }>) {
    return request<{ todo: Todo }>(`/todos/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    })
  },

  toggleTodo(id: number) {
    return request<{ todo: Todo }>(`/todos/${id}/toggle`, { method: 'PATCH' })
  },

  deleteTodo(id: number) {
    return request(`/todos/${id}`, { method: 'DELETE' })
  },
}
