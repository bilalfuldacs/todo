import { Navigate, useNavigate } from 'react-router-dom'
import { AuthForm } from '../components/AuthForm'
import { useAuth } from '../context/AuthContext'

export function RegisterPage() {
  const { register, user } = useAuth()
  const navigate = useNavigate()

  if (user) {
    return <Navigate to="/" replace />
  }

  return (
    <AuthForm
      mode="register"
      onSubmit={async ({ name, email, password }) => {
        await register(name!, email, password)
        navigate('/')
      }}
    />
  )
}
