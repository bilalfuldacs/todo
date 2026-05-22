import { useState, type FormEvent } from 'react'
import { Link } from 'react-router-dom'
import { ApiError } from '../api/client'

interface AuthFormProps {
  mode: 'login' | 'register'
  onSubmit: (data: { name?: string; email: string; password: string }) => Promise<void>
}

export function AuthForm({ mode, onSubmit }: AuthFormProps) {
  const [name, setName] = useState('')
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState<string | null>(null)
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({})
  const [submitting, setSubmitting] = useState(false)

  async function handleSubmit(e: FormEvent) {
    e.preventDefault()
    setError(null)
    setFieldErrors({})
    setSubmitting(true)

    try {
      await onSubmit({
        name: mode === 'register' ? name : undefined,
        email,
        password,
      })
    } catch (err) {
      if (err instanceof ApiError) {
        setError(err.message)
        if (err.errors) setFieldErrors(err.errors)
      } else {
        setError('Something went wrong. Is the API running?')
      }
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <div className="auth-card">
      <h1>{mode === 'login' ? 'Welcome back' : 'Create account'}</h1>
      <p className="auth-subtitle">
        {mode === 'login'
          ? 'Sign in to manage your todos'
          : 'Register to start organizing tasks'}
      </p>

      <form onSubmit={handleSubmit} className="auth-form" noValidate>
        {mode === 'register' && (
          <label>
            Name
            <input
              type="text"
              value={name}
              onChange={(e) => setName(e.target.value)}
              autoComplete="name"
              required
            />
            {fieldErrors.name && <span className="field-error">{fieldErrors.name}</span>}
          </label>
        )}

        <label>
          Email
          <input
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            autoComplete="email"
            required
          />
          {fieldErrors.email && <span className="field-error">{fieldErrors.email}</span>}
        </label>

        <label>
          Password
          <input
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            autoComplete={mode === 'login' ? 'current-password' : 'new-password'}
            minLength={8}
            required
          />
          {fieldErrors.password && (
            <span className="field-error">{fieldErrors.password}</span>
          )}
        </label>

        {error && <p className="form-error" role="alert">{error}</p>}

        <button type="submit" className="btn btn-primary btn-block" disabled={submitting}>
          {submitting ? 'Please wait…' : mode === 'login' ? 'Sign in' : 'Sign up'}
        </button>
      </form>

      <p className="auth-switch">
        {mode === 'login' ? (
          <>
            No account? <Link to="/register">Register</Link>
          </>
        ) : (
          <>
            Already have an account? <Link to="/login">Sign in</Link>
          </>
        )}
      </p>
    </div>
  )
}
