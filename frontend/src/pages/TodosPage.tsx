import { useCallback, useEffect, useState } from 'react'
import { api, ApiError } from '../api/client'
import { TodoForm } from '../components/TodoForm'
import { TodoList } from '../components/TodoList'
import type { Todo } from '../types'

type Filter = 'all' | 'active' | 'completed'

function completedParam(filter: Filter): '0' | '1' | undefined {
  if (filter === 'all') return undefined
  return filter === 'completed' ? '1' : '0'
}

export function TodosPage() {
  const [todos, setTodos] = useState<Todo[]>([])
  const [filter, setFilter] = useState<Filter>('all')
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    let cancelled = false

    async function fetchTodos() {
      try {
        const res = await api.getTodos(completedParam(filter))
        if (cancelled) return
        setTodos(res.data?.todos ?? [])
        setError(null)
      } catch (err) {
        if (cancelled) return
        setError(err instanceof ApiError ? err.message : 'Failed to load todos')
      } finally {
        if (!cancelled) setLoading(false)
      }
    }

    void fetchTodos()

    return () => {
      cancelled = true
    }
  }, [filter])

  const reloadTodos = useCallback(async () => {
    setLoading(true)
    setError(null)
    try {
      const res = await api.getTodos(completedParam(filter))
      setTodos(res.data?.todos ?? [])
    } catch (err) {
      setError(err instanceof ApiError ? err.message : 'Failed to load todos')
    } finally {
      setLoading(false)
    }
  }, [filter])

  function selectFilter(next: Filter) {
    setLoading(true)
    setFilter(next)
  }

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
        await reloadTodos()
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
            onClick={() => selectFilter(f)}
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
