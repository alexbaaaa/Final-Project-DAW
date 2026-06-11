import { useMemo, useState } from 'react'

function PasswordChange({ apiBaseUrl, onLogout, onPasswordChanged, session }) {
  const [currentPassword, setCurrentPassword] = useState('')
  const [password, setPassword] = useState('')
  const [passwordConfirmation, setPasswordConfirmation] = useState('')
  const [error, setError] = useState('')
  const [isSubmitting, setIsSubmitting] = useState(false)

  const userName = useMemo(() => {
    return session?.user?.full_name || session?.user?.alias || 'Swimming Up user'
  }, [session])

  const handleSubmit = async (event) => {
    event.preventDefault()
    setError('')

    const validationError = validatePasswordForm({
      currentPassword,
      password,
      passwordConfirmation,
    })

    if (validationError) {
      setError(validationError)

      return
    }

    setIsSubmitting(true)

    try {
      const response = await fetch(`${apiBaseUrl}/front/change-password`, {
        method: 'POST',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          user_id: session.user.id,
          current_password: currentPassword,
          password,
          password_confirmation: passwordConfirmation,
        }),
      })
      const payload = await response.json()

      if (!response.ok) {
        throw new Error(payload.message || getValidationMessage(payload.errors) || 'Password could not be changed.')
      }

      onPasswordChanged(payload.data)
    } catch (passwordError) {
      setError(passwordError.message || 'Password could not be changed.')
    } finally {
      setIsSubmitting(false)
    }
  }

  return (
    <main className="login-page password-change-page">
      <div className="pool-scene" aria-hidden="true">
        <span className="lane lane-1" />
        <span className="lane lane-2" />
        <span className="lane lane-3" />
        <span className="lane lane-4" />
        <span className="wake wake-1" />
        <span className="wake wake-2" />
      </div>

      <button className="back-link" type="button" onClick={onLogout}>
        Log out
      </button>

      <section className="login-card password-change-card" aria-labelledby="password-change-title">
        <p className="home-eyebrow">Swimming Up</p>
        <h1 id="password-change-title">Change password</h1>
        <p className="login-copy">{userName}</p>

        <form className="login-form" onSubmit={handleSubmit}>
          <div className="form-field">
            <label htmlFor="current_password">Current Password</label>
            <input
              autoComplete="current-password"
              id="current_password"
              name="current_password"
              required
              type="password"
              value={currentPassword}
              onChange={(event) => setCurrentPassword(event.target.value)}
            />
          </div>

          <div className="form-field">
            <label htmlFor="new_password">New Password</label>
            <input
              autoComplete="new-password"
              id="new_password"
              name="password"
              required
              type="password"
              value={password}
              onChange={(event) => setPassword(event.target.value)}
            />
          </div>

          <div className="form-field">
            <label htmlFor="password_confirmation">Repeat Password</label>
            <input
              autoComplete="new-password"
              id="password_confirmation"
              name="password_confirmation"
              required
              type="password"
              value={passwordConfirmation}
              onChange={(event) => setPasswordConfirmation(event.target.value)}
            />
          </div>

          {error && <p className="login-error">{error}</p>}

          <button className="start-button login-submit" disabled={isSubmitting} type="submit">
            {isSubmitting ? 'Changing password...' : 'Continue'}
          </button>
        </form>
      </section>
    </main>
  )
}

function validatePasswordForm({ currentPassword, password, passwordConfirmation }) {
  if (!currentPassword) {
    return 'Current password is required.'
  }

  if (password.length < 8) {
    return 'The new password must have at least 8 characters.'
  }

  if (!/[A-Z]/.test(password)) {
    return 'The new password must include at least one uppercase letter.'
  }

  if (!/[^A-Za-z0-9]/.test(password)) {
    return 'The new password must include at least one special character.'
  }

  if (password !== passwordConfirmation) {
    return 'The repeated password does not match.'
  }

  return ''
}

function getValidationMessage(errors) {
  if (!errors || typeof errors !== 'object') {
    return ''
  }

  const firstField = Object.keys(errors)[0]
  const firstError = firstField ? errors[firstField]?.[0] : ''

  return firstError || ''
}

export default PasswordChange
