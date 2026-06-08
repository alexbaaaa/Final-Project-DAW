import { useState } from 'react'

function Login({ apiBaseUrl, onBack, onLogin }) {
  const [alias, setAlias] = useState('')
  const [password, setPassword] = useState('')
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [error, setError] = useState('')

  const handleSubmit = async (event) => {
    event.preventDefault()
    setError('')
    setIsSubmitting(true)

    try {
      const response = await fetch(`${apiBaseUrl}/front/login`, {
        method: 'POST',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          alias,
          password,
        }),
      })

      const payload = await response.json()

      if (!response.ok) {
        throw new Error(payload.message || 'Invalid credentials.')
      }

      onLogin(payload.data)
    } catch (loginError) {
      setError(loginError.message || 'The login request could not be completed.')
    } finally {
      setIsSubmitting(false)
    }
  }

  return (
    <main className="login-page">
      <div className="pool-scene" aria-hidden="true">
        <span className="lane lane-1" />
        <span className="lane lane-2" />
        <span className="lane lane-3" />
        <span className="lane lane-4" />
        <span className="wake wake-1" />
        <span className="wake wake-2" />
      </div>

      <button className="back-link" type="button" onClick={onBack}>
        Back
      </button>

      <section className="login-card" aria-labelledby="login-title">
        <p className="home-eyebrow">Swimming Up</p>
        <h1 id="login-title">Sign in</h1>
        <p className="login-copy">Access your swimmer dashboard and club calendar.</p>

        <form className="login-form" onSubmit={handleSubmit}>
          <div className="form-field">
            <label htmlFor="alias">Alias</label>
            <input
              autoComplete="username"
              id="alias"
              name="alias"
              required
              type="text"
              value={alias}
              onChange={(event) => setAlias(event.target.value)}
            />
          </div>

          <div className="form-field">
            <label htmlFor="password">Password</label>
            <input
              id="password"
              name="password"
              required
              type="password"
              value={password}
              onChange={(event) => setPassword(event.target.value)}
            />
          </div>

          {error && <p className="login-error">{error}</p>}

          <button className="start-button login-submit" disabled={isSubmitting} type="submit">
            {isSubmitting ? 'Signing in...' : 'Sign in'}
          </button>
        </form>
      </section>
    </main>
  )
}

export default Login
