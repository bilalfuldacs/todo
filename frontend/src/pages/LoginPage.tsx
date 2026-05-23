import { Navigate, useNavigate } from 'react-router-dom'
import { AuthForm } from '../components/AuthForm'
import { useAuth } from '../hooks/useAuth'

export function LoginPage() {
  const { login, user } = useAuth()
  const navigate = useNavigate()

  if (user) {
    return <Navigate to="/" replace />
  }

  return (
    <AuthForm
      mode="login"
      onSubmit={async ({ email, password }) => {
        await login(email, password)
        navigate('/')
      }}
    />
  )
}
