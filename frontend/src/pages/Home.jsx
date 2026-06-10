function Home({ onStart }) {
  return (
    <main className="home-page">
      <section className="home-hero" aria-labelledby="home-title">
        <div className="pool-scene" aria-hidden="true">
          <span className="lane lane-1" />
          <span className="lane lane-2" />
          <span className="lane lane-3" />
          <span className="lane lane-4" />
          <span className="wake wake-1" />
          <span className="wake wake-2" />
        </div>

        <div className="home-content">
          <p className="home-eyebrow">Swimming performance app</p>

          <h1 id="home-title" className="home-title">
            Welcome to Swimming{' '}
            <span className="flip-word" aria-label="Up, App">
              <span className="flip-word-inner">
                <span className="flip-face flip-face-front">Up</span>
                <span className="flip-face flip-face-back">App</span>
              </span>
            </span>
          </h1>

          <p className="home-description">
            Swimming Up helps athletes level up in the pool while working like a
            swimming app for daily progress. Track improvement, focus training,
            and make every result easier to understand.
          </p>

          <button className="start-button" type="button" onClick={onStart}>
            Let&apos;s get started
          </button>
        </div>
      </section>
    </main>
  )
}

export default Home
