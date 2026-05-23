export interface User {
  id: number
  name: string
  email: string
  created_at: string
}

export interface Todo {
  id: number
  title: string
  description: string | null
  completed: number | boolean
  created_at: string
  updated_at: string
}

export interface AuthPayload {
  user: User
  token: string
  token_type: string
  expires_in_days: number
}

export interface ApiSuccess<T = unknown> {
  success: true
  message?: string
  data?: T
}

export interface ApiErrorBody {
  success: false
  message: string
  errors?: Record<string, string>
}
