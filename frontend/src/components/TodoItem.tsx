import { useState } from 'react'
import type { Todo } from '../types'

interface TodoItemProps {
  todo: Todo
  onToggle: (id: number) => Promise<void>
  onUpdate: (id: number, title: string, description: string) => Promise<void>
  onDelete: (id: number) => Promise<void>
}

function isCompleted(todo: Todo): boolean {
  return todo.completed === 1 || todo.completed === true
}

export function TodoItem({ todo, onToggle, onUpdate, onDelete }: TodoItemProps) {
  const [editing, setEditing] = useState(false)
  const [title, setTitle] = useState(todo.title)
  const [description, setDescription] = useState(todo.description ?? '')
  const [busy, setBusy] = useState(false)

  async function saveEdit() {
    if (!title.trim()) return
    setBusy(true)
    try {
      await onUpdate(todo.id, title.trim(), description.trim())
      setEditing(false)
    } finally {
      setBusy(false)
    }
  }

  async function handleToggle() {
    setBusy(true)
    try {
      await onToggle(todo.id)
    } finally {
      setBusy(false)
    }
  }

  async function handleDelete() {
    if (!confirm('Delete this todo?')) return
    setBusy(true)
    try {
      await onDelete(todo.id)
    } finally {
      setBusy(false)
    }
  }

  const done = isCompleted(todo)

  return (
    <li className={`todo-item ${done ? 'todo-item--done' : ''} ${busy ? 'todo-item--busy' : ''}`}>
      <button
        type="button"
        className="todo-check"
        onClick={handleToggle}
        aria-label={done ? 'Mark incomplete' : 'Mark complete'}
        disabled={busy}
      >
        {done ? '✓' : ''}
      </button>

      {editing ? (
        <div className="todo-edit">
          <input value={title} onChange={(e) => setTitle(e.target.value)} />
          <input
            value={description}
            onChange={(e) => setDescription(e.target.value)}
            placeholder="Description"
          />
          <div className="todo-edit-actions">
            <button type="button" className="btn btn-primary btn-sm" onClick={saveEdit} disabled={busy}>
              Save
            </button>
            <button
              type="button"
              className="btn btn-ghost btn-sm"
              onClick={() => {
                setEditing(false)
                setTitle(todo.title)
                setDescription(todo.description ?? '')
              }}
            >
              Cancel
            </button>
          </div>
        </div>
      ) : (
        <div className="todo-content">
          <strong>{todo.title}</strong>
          {todo.description && <p>{todo.description}</p>}
        </div>
      )}

      {!editing && (
        <div className="todo-actions">
          <button type="button" className="btn btn-ghost btn-sm" onClick={() => setEditing(true)}>
            Edit
          </button>
          <button type="button" className="btn btn-danger btn-sm" onClick={handleDelete} disabled={busy}>
            Delete
          </button>
        </div>
      )}
    </li>
  )
}
