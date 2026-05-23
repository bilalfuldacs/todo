import { useState, type FormEvent } from 'react'

interface TodoFormProps {
  onAdd: (title: string, description: string) => Promise<void>
}

export function TodoForm({ onAdd }: TodoFormProps) {
  const [title, setTitle] = useState('')
  const [description, setDescription] = useState('')
  const [submitting, setSubmitting] = useState(false)

  async function handleSubmit(e: FormEvent) {
    e.preventDefault()
    if (!title.trim()) return

    setSubmitting(true)
    try {
      await onAdd(title.trim(), description.trim())
      setTitle('')
      setDescription('')
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <form className="todo-form" onSubmit={handleSubmit}>
      <input
        type="text"
        placeholder="What needs to be done?"
        value={title}
        onChange={(e) => setTitle(e.target.value)}
        aria-label="Todo title"
      />
      <input
        type="text"
        placeholder="Description (optional)"
        value={description}
        onChange={(e) => setDescription(e.target.value)}
        aria-label="Todo description"
      />
      <button type="submit" className="btn btn-primary" disabled={submitting || !title.trim()}>
        Add
      </button>
    </form>
  )
}
