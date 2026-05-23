import { useCallback, useEffect, useState } from 'react'
import { api, ApiError } from '../api/client'
import { TodoForm } from '../components/TodoForm'
import { TodoList } from '../components/TodoList'
import type { Todo } from '../types'

type Filter = 'all' | 'active' | 'completed'

export function TodosPage() {
  const [todos, setTodos] = useState<Todo[]>([])
  const [filter, setFilter] = useState<Filter>('all')
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  const loadTodos = useCallback(async () => {
    setError(null)
    try {
      const completed =
        filter === 'all' ? undefined : filter === 'completed' ? ('1' as const) : ('0' as const)
      const res = await api.getTodos(completed)
      setTodos(res.data?.todos ?? [])
    } catch (err) {
      setError(err instanceof ApiError ? err.message : 'Failed to load todos')
    } finally {
      setLoading(false)
    }
  }, [filter])

  useEffect(() => {
    setLoading(true)
    loadTodos()
  }, [loadTodos])

  async function handleAdd(title: string, description: string) {
    const res = await api.createTodo(title, description)
    if (res.data?.todo) {
      setTodos((prev) => [res.data!.todo, ...prev])
    }
  }

  async function handleToggle(id: number) {
    const res = await api.toggleTodo(id)
    if (res.data?.todo) {
      setTodos((prev) => prev.map((t) => (t.id === id ? res.data!.todo : t)))
      if (filter !== 'all') {
        await loadTodos()
      }
    }
  }

  async function handleUpdate(id: number, title: string, description: string) {
    const res = await api.updateTodo(id, { title, description })
    if (res.data?.todo) {
      setTodos((prev) => prev.map((t) => (t.id === id ? res.data!.todo : t)))
    }
  }

  async function handleDelete(id: number) {
    await api.deleteTodo(id)
    setTodos((prev) => prev.filter((t) => t.id !== id))
  }

  return (
    <div className="todos-page">
      <div className="page-head">
        <h1>My tasks</h1>
        <p>Stay organized with your personal todo list</p>
      </div>

      <TodoForm onAdd={handleAdd} />

      <div className="filter-tabs" role="tablist">
        {(['all', 'active', 'completed'] as Filter[]).map((f) => (
          <button
            key={f}
            type="button"
            role="tab"
            aria-selected={filter === f}
            className={filter === f ? 'active' : ''}
            onClick={() => setFilter(f)}
          >
            {f.charAt(0).toUpperCase() + f.slice(1)}
          </button>
        ))}
      </div>

      {error && <p className="form-error" role="alert">{error}</p>}

      {loading ? (
        <div className="loading-inline">
          <div className="spinner" aria-hidden />
          Loading todos…
        </div>
      ) : (
        <TodoList
          todos={todos}
          onToggle={handleToggle}
          onUpdate={handleUpdate}
          onDelete={handleDelete}
        />
      )}
    </div>
  )
}
