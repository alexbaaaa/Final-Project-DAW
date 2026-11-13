import { useEffect, useMemo, useState } from 'react'

const WEEK_DAYS = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
const TRAINING_DAY_VALUES = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']
const TRAINING_WEEK_HOURS = Array.from({ length: 18 }, (_, index) => index + 6)
const TIME_DISTANCES_BY_STYLE = {
  Libres: ['50m', '100m', '200m', '400m', '800m', '1500m'],
  Espalda: ['50m', '100m', '200m'],
  Braza: ['50m', '100m', '200m'],
  Mariposa: ['50m', '100m', '200m'],
  Estilos: ['200m', '400m'],
}

function AppIndex({ apiBaseUrl, path, session, onLogout, onNavigate, onPasswordRequired }) {
  const [monthDate, setMonthDate] = useState(() => startOfMonth(new Date()))
  const [appData, setAppData] = useState(null)
  const [isLoading, setIsLoading] = useState(true)
  const [error, setError] = useState('')
  const [selectedEvent, setSelectedEvent] = useState(null)
  const [timeStyleFilter, setTimeStyleFilter] = useState('All')
  const [timeDistanceFilter, setTimeDistanceFilter] = useState('All')
  const [pendingTimeStyleFilter, setPendingTimeStyleFilter] = useState('All')
  const [pendingTimeDistanceFilter, setPendingTimeDistanceFilter] = useState('All')
  const [timeSortOrder, setTimeSortOrder] = useState('recent')

  const monthKey = useMemo(() => formatMonthKey(monthDate), [monthDate])
  const selectedSwimmerId = getSelectedSwimmerId(session)

  useEffect(() => {
    const controller = new AbortController()

    const loadAppData = async () => {
      setIsLoading(true)
      setError('')

      try {
        const params = new URLSearchParams({
          user_id: session.user.id,
          month: monthKey,
        })

        if (selectedSwimmerId) {
          params.set('swimmer_id', selectedSwimmerId)
        }

        const response = await fetch(`${apiBaseUrl}/front/app-data?${params}`, {
          headers: {
            Accept: 'application/json',
          },
          signal: controller.signal,
        })
        const payload = await response.json()

        if (response.status === 403 && payload.message === 'Password change is required before accessing the application.') {
          onPasswordRequired()

          return
        }

        if (!response.ok) {
          throw new Error(payload.message || 'Calendar data could not be loaded.')
        }

        setAppData(payload.data)
      } catch (requestError) {
        if (requestError.name !== 'AbortError') {
          setError(requestError.message || 'Calendar data could not be loaded.')
        }
      } finally {
        if (!controller.signal.aborted) {
          setIsLoading(false)
        }
      }
    }

    loadAppData()

    return () => controller.abort()
  }, [apiBaseUrl, monthKey, onPasswordRequired, selectedSwimmerId, session.user.id])

  const events = useMemo(() => appData?.events || [], [appData])
  const calendar = useMemo(() => appData?.calendar || [], [appData])
  const times = useMemo(() => appData?.times || [], [appData])
  const trainingGroup = appData?.training_group || null
  const linkedSwimmers = getLinkedSwimmers(appData, session)
  const swimmer = getSelectedSwimmer(appData, session, linkedSwimmers)
  const activeSwimmers = swimmer ? [swimmer] : linkedSwimmers
  const categorySummary = formatUniqueList(activeSwimmers.map((linkedSwimmer) => linkedSwimmer.category))
  const isControlUser = session.user.user_type === 'other'
  const canChangeProfile = session.user.user_type === 'legal_guardian' && linkedSwimmers.length > 0
  const nextCompetition = useMemo(() => buildNextCompetition(events), [events])
  const recentEvents = useMemo(() => buildRecentEvents(events), [events])
  const filteredTimes = useMemo(
    () => buildFilteredTimes(times, timeStyleFilter, timeDistanceFilter, timeSortOrder),
    [timeDistanceFilter, timeSortOrder, timeStyleFilter, times],
  )
  const distanceOptions = useMemo(() => getDistanceOptions(pendingTimeStyleFilter), [pendingTimeStyleFilter])
  const welcomeName = swimmer?.full_name || session.user.full_name || session.user.alias || 'Swimming Up user'
  const calendarDays = useMemo(
    () => buildCalendarDays(monthDate, events, calendar),
    [calendar, events, monthDate],
  )
  const trainingWeekDays = useMemo(() => buildCurrentWeekDays(new Date()), [])
  const trainingWeekItems = useMemo(
    () => buildTrainingWeekItems(trainingGroup, events, calendar, trainingWeekDays),
    [calendar, events, trainingGroup, trainingWeekDays],
  )
  const currentSection = getSection(path, isControlUser)

  const goToPreviousMonth = () => {
    setMonthDate((current) => new Date(current.getFullYear(), current.getMonth() - 1, 1))
  }

  const goToNextMonth = () => {
    setMonthDate((current) => new Date(current.getFullYear(), current.getMonth() + 1, 1))
  }

  const handleTimeStyleChange = (event) => {
    setPendingTimeStyleFilter(event.target.value)
    setPendingTimeDistanceFilter('All')
  }

  const applyTimeFilters = () => {
    setTimeStyleFilter(pendingTimeStyleFilter)
    setTimeDistanceFilter(pendingTimeDistanceFilter)
  }

  return (
    <main className="app-index-page">
      <header className="app-header">
        {currentSection === 'calendar' ? (
          <div className="app-welcome">
            <p>Welcome</p>
            <h1>{welcomeName}</h1>
          </div>
        ) : (
          <button className="app-home-link" type="button" onClick={() => onNavigate('/app')}>
            Swimming Up
          </button>
        )}

        <nav className="app-nav" aria-label="Application navigation">
          <button
            className="nav-menu-button"
            aria-label="Open navigation menu"
            aria-haspopup="true"
            type="button"
          >
            <span aria-hidden="true" />
            <span aria-hidden="true" />
            <span aria-hidden="true" />
          </button>

          <div className="nav-menu">
            {!isControlUser && (
              <button type="button" onClick={() => handleMenuNavigation('/app/profile')}>
                Profile
              </button>
            )}
            {!isControlUser && canChangeProfile && (
              <button type="button" onClick={() => handleMenuNavigation('/app/select-profile')}>
                Change profile
              </button>
            )}
            {!isControlUser && (
              <button type="button" onClick={() => handleMenuNavigation('/app/training-group')}>
                Training group
              </button>
            )}
            <button type="button" onClick={() => handleMenuNavigation('/app')}>
              Main page
            </button>
            <button className="logout-item" type="button" onClick={onLogout}>
              Log out
            </button>
          </div>
        </nav>
      </header>

      {currentSection === 'calendar' && (
        <section className="calendar-layout" aria-labelledby="calendar-title">
          <aside className="recent-events-sidebar" aria-label="Recent events">
            <section className="sidebar-section" aria-labelledby="next-competition-title">
              <p className="home-eyebrow" id="next-competition-title">Next Competition</p>

              {isLoading && <p className="sidebar-feedback">Loading competition...</p>}
              {!isLoading && !error && !nextCompetition && (
                <p className="sidebar-feedback">No competition found.</p>
              )}
              {!isLoading && error && (
                <p className="sidebar-feedback sidebar-feedback-error">Competition could not be loaded.</p>
              )}

              {!isLoading && !error && nextCompetition && (
                <button
                  className="next-competition-card"
                  type="button"
                  onClick={() => setSelectedEvent(nextCompetition)}
                >
                  <strong>{nextCompetition.event_name}</strong>
                  <span>{formatEventDateRange(nextCompetition)}</span>
                  <small>{formatDayScope(nextCompetition.day_scope)}</small>
                </button>
              )}
            </section>

            <section className="sidebar-section" aria-labelledby="recent-events-title">
              <p className="home-eyebrow" id="recent-events-title">Recent events</p>

              {isLoading && <p className="sidebar-feedback">Loading events...</p>}
              {!isLoading && !error && recentEvents.length === 0 && (
                <p className="sidebar-feedback">No recent events found.</p>
              )}
              {!isLoading && error && (
                <p className="sidebar-feedback sidebar-feedback-error">Events could not be loaded.</p>
              )}

              {!isLoading && !error && recentEvents.length > 0 && (
                <div className="recent-event-list">
                  {recentEvents.map((event) => (
                    <button
                      className="recent-event-item"
                      key={event.id}
                      type="button"
                      onClick={() => setSelectedEvent(event)}
                    >
                      <strong>{event.event_name}</strong>
                      <span>Added {formatReadableDate(event.created_at || event.event_date)}</span>
                    </button>
                  ))}
                </div>
              )}
            </section>
          </aside>

          <div className="calendar-main">
            <div className="calendar-panel">
              <div className="calendar-toolbar">
                <div>
                  <p className="home-eyebrow">Club calendar</p>
                  <h1 id="calendar-title">{formatMonthTitle(monthDate)}</h1>
                  <p>
                    {categorySummary
                      ? `Events for ${categorySummary}`
                      : 'No swimmer profile is linked to this user yet.'}
                  </p>
                </div>

                <div className="month-actions">
                  <button type="button" onClick={goToPreviousMonth}>
                    Previous
                  </button>
                  <button type="button" onClick={goToNextMonth}>
                    Next
                  </button>
                </div>
              </div>

              {isLoading && <p className="calendar-feedback">Loading calendar...</p>}
              {error && <p className="calendar-feedback calendar-feedback-error">{error}</p>}

              {!isLoading && !error && (
                <div className="calendar-grid" role="grid" aria-label={formatMonthTitle(monthDate)}>
                  {WEEK_DAYS.map((weekDay) => (
                    <div className="week-day" key={weekDay}>
                      {weekDay}
                    </div>
                  ))}

                  {calendarDays.map((day) => (
                    <article
                      className={day.className}
                      key={day.key}
                      role="gridcell"
                      aria-label={day.label}
                    >
                      {day.date && (
                        <>
                          <span className="day-number">{day.date.getDate()}</span>
                          {day.specialDay && (
                            <span className="day-state">{getCalendarTypeLabel(day.specialDay.day_type)}</span>
                          )}
                          <div className="day-events">
                            {day.events.map((event) => (
                              <button
                                className="event-chip"
                                key={event.id}
                                type="button"
                                onClick={() => setSelectedEvent(event)}
                              >
                                <strong>{event.event_name}</strong>
                                <span>{formatDayScope(event.day_scope)}</span>
                              </button>
                            ))}
                          </div>
                        </>
                      )}
                    </article>
                  ))}
                </div>
              )}
            </div>

            <aside className="calendar-legend" aria-label="Calendar legend">
              <span><i className="legend-event" /> Club event</span>
              <span><i className="legend-holiday-training" /> Holiday with training</span>
              <span><i className="legend-holiday-no-training" /> Holiday without training</span>
              <span><i className="legend-registration-deadline" /> Registration deadline</span>
              <span><i className="legend-schedule-change" /> Schedule change</span>
              <span><i className="legend-suspended" /> Training suspended</span>
            </aside>
          </div>
        </section>
      )}

      {currentSection === 'profile' && !isControlUser && (
        <ProfilePage
          distanceOptions={distanceOptions}
          filteredTimes={filteredTimes}
          onDistanceChange={(event) => setPendingTimeDistanceFilter(event.target.value)}
          onFilterApply={applyTimeFilters}
          onNavigate={onNavigate}
          onSortToggle={() => setTimeSortOrder((currentOrder) => (currentOrder === 'recent' ? 'oldest' : 'recent'))}
          onStyleChange={handleTimeStyleChange}
          selectedDistance={pendingTimeDistanceFilter}
          selectedStyle={pendingTimeStyleFilter}
          sortOrder={timeSortOrder}
          swimmer={swimmer}
          times={times}
          trainingGroup={trainingGroup}
        />
      )}

      {currentSection === 'training' && (
        <TrainingGroupPage
          categorySummary={categorySummary}
          error={error}
          isLoading={isLoading}
          trainingGroup={trainingGroup}
          weekDays={trainingWeekDays}
          weekItems={trainingWeekItems}
        />
      )}

      {selectedEvent && (
        <div className="event-modal" role="dialog" aria-modal="true" aria-labelledby="event-title">
          <div className="event-modal-card">
            <button className="modal-close" type="button" onClick={() => setSelectedEvent(null)}>
              Close
            </button>
            <p className="home-eyebrow">{formatEventDateRange(selectedEvent)}</p>
            <h2 id="event-title">{selectedEvent.event_name}</h2>
            <p>{selectedEvent.description}</p>
            <strong>Day scope</strong>
            <span>{formatDayScope(selectedEvent.day_scope)}</span>
            <strong>Categories</strong>
            <span>{selectedEvent.categories}</span>
          </div>
        </div>
      )}
    </main>
  )

  function handleMenuNavigation(nextPath) {
    onNavigate(nextPath)
  }
}

function buildCalendarDays(monthDate, events, calendar) {
  const year = monthDate.getFullYear()
  const month = monthDate.getMonth()
  const firstDay = new Date(year, month, 1)
  const daysInMonth = new Date(year, month + 1, 0).getDate()
  const leadingDays = (firstDay.getDay() + 6) % 7
  const cells = []
  const eventsByDate = groupEventsByDate(events)
  const calendarByDate = groupByDate(calendar, 'date')

  for (let index = 0; index < leadingDays; index += 1) {
    cells.push({
      className: 'calendar-day calendar-day-empty',
      date: null,
      key: `empty-start-${index}`,
      label: 'Empty day',
    })
  }

  for (let day = 1; day <= daysInMonth; day += 1) {
    const date = new Date(year, month, day)
    const dateKey = formatDateKey(date)
    const dayEvents = eventsByDate[dateKey] || []
    const specialDay = (calendarByDate[dateKey] || [])[0]
    const classes = ['calendar-day']

    if (dayEvents.length > 0) {
      classes.push('calendar-day-event')
    }

    if (specialDay) {
      classes.push(`calendar-day-${specialDay.day_type.replace(/_/g, '-')}`)
    }

    cells.push({
      className: classes.join(' '),
      date,
      events: dayEvents,
      key: dateKey,
      label: `${dateKey}${dayEvents.length ? `, ${dayEvents.length} event` : ''}`,
      specialDay,
    })
  }

  while (cells.length % 7 !== 0) {
    cells.push({
      className: 'calendar-day calendar-day-empty',
      date: null,
      key: `empty-end-${cells.length}`,
      label: 'Empty day',
    })
  }

  return cells
}

function groupByDate(records, dateField) {
  return records.reduce((groupedRecords, record) => {
    const dateKey = record[dateField]

    if (!groupedRecords[dateKey]) {
      groupedRecords[dateKey] = []
    }

    groupedRecords[dateKey].push(record)

    return groupedRecords
  }, {})
}

function groupEventsByDate(events) {
  return events.reduce((groupedEvents, event) => {
    const startDate = parseDateValue(event.event_start_date || event.event_date)
    const endDate = parseDateValue(event.event_end_date || event.event_start_date || event.event_date) || startDate

    if (!startDate) {
      return groupedEvents
    }

    const currentDate = new Date(startDate)

    while (currentDate <= endDate) {
      const dateKey = formatDateKey(currentDate)

      if (!groupedEvents[dateKey]) {
        groupedEvents[dateKey] = []
      }

      groupedEvents[dateKey].push(event)
      currentDate.setDate(currentDate.getDate() + 1)
    }

    return groupedEvents
  }, {})
}

function buildCurrentWeekDays(date) {
  const startDate = startOfWeek(date)

  return TRAINING_DAY_VALUES.map((dayValue, index) => {
    const dayDate = new Date(startDate)
    dayDate.setDate(startDate.getDate() + index)

    return {
      date: dayDate,
      dateKey: formatDateKey(dayDate),
      label: new Intl.DateTimeFormat('en-US', { weekday: 'short' }).format(dayDate),
      value: dayValue,
    }
  })
}

function buildTrainingWeekItems(trainingGroup, events, calendar, weekDays) {
  const itemsByDate = weekDays.reduce((items, day) => {
    items[day.dateKey] = []

    return items
  }, {})

  addTrainingBlocks(itemsByDate, trainingGroup, weekDays)
  addEventBlocks(itemsByDate, events, weekDays)
  addCalendarBlocks(itemsByDate, calendar)

  Object.keys(itemsByDate).forEach((dateKey) => {
    itemsByDate[dateKey] = withStackedLayout(itemsByDate[dateKey])
  })

  return itemsByDate
}

function addTrainingBlocks(itemsByDate, trainingGroup, weekDays) {
  const schedule = Array.isArray(trainingGroup?.schedule) ? trainingGroup.schedule : []

  schedule.forEach((entry, entryIndex) => {
    const days = Array.isArray(entry.days) ? entry.days : []
    const startHour = parseScheduleHour(entry.from)
    const endHour = parseScheduleHour(entry.to)

    if (days.length === 0 || startHour === null || endHour === null || endHour <= startHour) {
      return
    }

    weekDays
      .filter((day) => days.includes(day.value))
      .forEach((day) => {
        itemsByDate[day.dateKey].push({
          id: `training-${entryIndex}-${day.dateKey}`,
          className: 'training-block-training',
          endHour,
          startHour,
          subtitle: `${formatHourValue(startHour)} - ${formatHourValue(endHour)}`,
          title: 'Training',
        })
      })
  })
}

function addEventBlocks(itemsByDate, events, weekDays) {
  events.forEach((event) => {
    const startDate = parseDateValue(event.event_start_date || event.event_date)
    const endDate = parseDateValue(event.event_end_date || event.event_start_date || event.event_date) || startDate

    if (!startDate) {
      return
    }

    weekDays
      .filter((day) => day.date >= startDate && day.date <= endDate)
      .forEach((day) => {
        const range = getDayScopeHourRange(event.day_scope)

        itemsByDate[day.dateKey].push({
          id: `event-${event.id}-${day.dateKey}`,
          className: 'training-block-event',
          endHour: range.end,
          startHour: range.start,
          subtitle: formatDayScope(event.day_scope),
          title: event.event_name || 'Event',
        })
      })
  })
}

function addCalendarBlocks(itemsByDate, calendar) {
  calendar.forEach((calendarDay) => {
    if (!itemsByDate[calendarDay.date]) {
      return
    }

    const range = getDayScopeHourRange(calendarDay.day_scope)
    const typeClass = String(calendarDay.day_type || '').replace(/_/g, '-')

    itemsByDate[calendarDay.date].push({
      id: `calendar-${calendarDay.id}-${calendarDay.date}`,
      className: `training-block-calendar training-block-${typeClass}`,
      endHour: range.end,
      startHour: range.start,
      subtitle: formatDayScope(calendarDay.day_scope),
      title: calendarDay.title || getCalendarTypeLabel(calendarDay.day_type),
    })
  })
}

function withStackedLayout(items) {
  return [...items]
    .sort((firstItem, secondItem) => firstItem.startHour - secondItem.startHour || firstItem.endHour - secondItem.endHour)
    .map((item, index, sortedItems) => {
      const overlappingItems = sortedItems.filter((candidateItem) => (
        candidateItem.startHour < item.endHour && candidateItem.endHour > item.startHour
      ))
      const stackIndex = overlappingItems.findIndex((candidateItem) => candidateItem.id === item.id)
      const stackTotal = Math.max(overlappingItems.length, 1)

      return {
        ...item,
        stackIndex: stackIndex === -1 ? index : stackIndex,
        stackTotal,
      }
    })
}

function getTrainingBlockStyle(item) {
  const startPercent = hourToWeekPercent(item.startHour)
  const endPercent = hourToWeekPercent(item.endHour)
  const width = 100 / item.stackTotal
  const left = item.stackIndex * width

  return {
    height: `${Math.max(endPercent - startPercent, 4)}%`,
    left: `calc(${left}% + 4px)`,
    top: `${startPercent}%`,
    width: `calc(${width}% - 8px)`,
  }
}

function getDayScopeHourRange(dayScope) {
  if (dayScope === 'morning') {
    return { start: 6, end: 14 }
  }

  if (dayScope === 'afternoon') {
    return { start: 14, end: 24 }
  }

  return { start: 6, end: 24 }
}

function hourToWeekPercent(hour) {
  const clampedHour = Math.min(Math.max(hour, 6), 24)

  return ((clampedHour - 6) / 18) * 100
}

function parseScheduleHour(value) {
  const [hours, minutes = '0'] = String(value || '').split(':')
  const parsedHours = Number.parseInt(hours, 10)
  const parsedMinutes = Number.parseInt(minutes, 10)

  if (Number.isNaN(parsedHours) || Number.isNaN(parsedMinutes)) {
    return null
  }

  return parsedHours + parsedMinutes / 60
}

function formatHourValue(value) {
  const hours = Math.floor(value)
  const minutes = Math.round((value - hours) * 60)

  return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`
}

function formatDateKey(date) {
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(
    date.getDate(),
  ).padStart(2, '0')}`
}

function formatMonthKey(date) {
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`
}

function formatMonthTitle(date) {
  return new Intl.DateTimeFormat('en-US', {
    month: 'long',
    year: 'numeric',
  }).format(date)
}

function formatEventDateRange(event) {
  const startDate = event.event_start_date || event.event_date
  const endDate = event.event_end_date || startDate

  if (!startDate) {
    return 'Date not available'
  }

  return startDate === endDate
    ? formatReadableDate(startDate)
    : `${formatReadableDate(startDate)} - ${formatReadableDate(endDate)}`
}

function buildNextCompetition(events) {
  const competitions = events
    .filter((event) => String(event.event_type || '').toLowerCase() === 'competition')
    .map((event) => ({
      event,
      startDate: parseDateValue(event.event_start_date || event.event_date),
    }))
    .filter(({ startDate }) => startDate)

  if (!competitions.length) {
    return null
  }

  const today = new Date()
  today.setHours(0, 0, 0, 0)

  const upcomingCompetitions = competitions
    .filter(({ startDate }) => startDate >= today)
    .sort((firstCompetition, secondCompetition) => firstCompetition.startDate - secondCompetition.startDate)

  if (upcomingCompetitions.length) {
    return upcomingCompetitions[0].event
  }

  return competitions
    .sort((firstCompetition, secondCompetition) => (
      Math.abs(firstCompetition.startDate - today) - Math.abs(secondCompetition.startDate - today)
    ))[0].event
}

function buildRecentEvents(events) {
  return [...events]
    .sort((firstEvent, secondEvent) => {
      const firstDate = new Date(firstEvent.created_at || firstEvent.event_date).getTime()
      const secondDate = new Date(secondEvent.created_at || secondEvent.event_date).getTime()

      return secondDate - firstDate
    })
    .slice(0, 5)
}

function formatReadableDate(value) {
  if (!value) {
    return 'date not available'
  }

  const date = new Date(value)

  if (Number.isNaN(date.getTime())) {
    return value
  }

  return new Intl.DateTimeFormat('en-US', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  }).format(date)
}

function formatDayScope(dayScope) {
  const labels = {
    afternoon: 'Afternoon only',
    full_day: 'Full day',
    morning: 'Morning only',
  }

  return labels[dayScope] || dayScope || 'Full day'
}

function formatWeekRange(weekDays) {
  if (!weekDays.length) {
    return ''
  }

  return `${formatReadableDate(weekDays[0].dateKey)} - ${formatReadableDate(weekDays[weekDays.length - 1].dateKey)}`
}

function buildFilteredTimes(times, selectedStyle, selectedDistance, sortOrder) {
  return times
    .map((timeRecord) => ({
      ...timeRecord,
      parsedTest: parseTimeTestType(timeRecord.test_type),
    }))
    .filter((timeRecord) => {
      const matchesStyle = selectedStyle === 'All' || timeRecord.parsedTest.style === selectedStyle
      const matchesDistance = selectedDistance === 'All' || timeRecord.parsedTest.distance === selectedDistance

      return matchesStyle && matchesDistance
    })
    .sort((firstTime, secondTime) => {
      const firstDate = parseDateValue(firstTime.date)?.getTime() || 0
      const secondDate = parseDateValue(secondTime.date)?.getTime() || 0

      return sortOrder === 'recent' ? secondDate - firstDate : firstDate - secondDate
    })
}

function getDistanceOptions(selectedStyle) {
  if (selectedStyle !== 'All') {
    return TIME_DISTANCES_BY_STYLE[selectedStyle] || []
  }

  return [...new Set(Object.values(TIME_DISTANCES_BY_STYLE).flat())]
}

function parseTimeTestType(testType) {
  const rawTestType = String(testType || '')
  const distanceMatch = rawTestType.match(/(50m|100m|200m|400m|800m|1500m)/i)
  const distance = distanceMatch ? distanceMatch[1].toLowerCase().replace('m', 'm') : ''
  const normalizedDistance = distance ? `${Number.parseInt(distance, 10)}m` : ''
  const styleAliases = {
    backstroke: 'Espalda',
    braza: 'Braza',
    breaststroke: 'Braza',
    butterfly: 'Mariposa',
    espalda: 'Espalda',
    estilos: 'Estilos',
    freestyle: 'Libres',
    libres: 'Libres',
    mariposa: 'Mariposa',
    medley: 'Estilos',
  }
  const styleFragment = rawTestType
    .replace(distanceMatch?.[0] || '', '')
    .replace(/-/g, ' ')
    .trim()
    .replace(/\s+/g, ' ')
    .toLowerCase()

  return {
    distance: normalizedDistance,
    style: styleAliases[styleFragment] || '',
  }
}

function getFirstName(swimmer) {
  if (swimmer?.first_name) {
    return swimmer.first_name
  }

  return String(swimmer?.full_name || '').split(' ').filter(Boolean)[0] || ''
}

function formatNullable(value) {
  if (value === null || value === undefined || value === '') {
    return 'null'
  }

  return String(value)
}

function getSelectedSwimmerId(session) {
  return session?.selected_swimmer_id ? String(session.selected_swimmer_id) : ''
}

function getSelectedSwimmer(appData, session, linkedSwimmers) {
  const selectedSwimmerId = getSelectedSwimmerId(session)

  if (!selectedSwimmerId) {
    return appData?.swimmer || session.swimmer || linkedSwimmers[0] || null
  }

  return (
    linkedSwimmers.find((linkedSwimmer) => String(linkedSwimmer.id) === selectedSwimmerId)
    || (String(appData?.swimmer?.id || '') === selectedSwimmerId ? appData.swimmer : null)
    || (String(session.swimmer?.id || '') === selectedSwimmerId ? session.swimmer : null)
    || null
  )
}

function ProfilePage({
  distanceOptions,
  filteredTimes,
  onDistanceChange,
  onFilterApply,
  onNavigate,
  onSortToggle,
  onStyleChange,
  selectedDistance,
  selectedStyle,
  sortOrder,
  swimmer,
  times,
  trainingGroup,
}) {
  const firstName = getFirstName(swimmer)
  const trainingGroupName = trainingGroup?.name || null

  return (
    <section className="profile-page" aria-labelledby="profile-title">
      <section className="profile-section" aria-labelledby="profile-card-title">
        <p className="home-eyebrow">Ficha</p>
        <div className="profile-heading">
          <h1 id="profile-title">{firstName || 'Profile'}</h1>
          <p>{formatNullable(swimmer?.category)} - Training group {formatNullable(trainingGroupName)}</p>
        </div>

        <div className="profile-data-card" id="profile-card-title">
          <dl>
            <div>
              <dt>Full name</dt>
              <dd>{formatNullable(swimmer?.full_name)}</dd>
            </div>
            <div>
              <dt>Birth date</dt>
              <dd>{formatReadableDate(swimmer?.birth_date)}</dd>
            </div>
            <div>
              <dt>Category</dt>
              <dd>{formatNullable(swimmer?.category)}</dd>
            </div>
            <div>
              <dt>Training group</dt>
              <dd>
                <button className="profile-inline-link" type="button" onClick={() => onNavigate('/app/training-group')}>
                  {formatNullable(trainingGroupName)}
                </button>
              </dd>
            </div>
            <div>
              <dt>Age</dt>
              <dd>{formatNullable(swimmer?.age)}</dd>
            </div>
            <div>
              <dt>Gender</dt>
              <dd>{formatNullable(swimmer?.gender)}</dd>
            </div>
          </dl>
        </div>
      </section>

      <section className="profile-section" aria-labelledby="profile-times-title">
        <div className="profile-section-header">
          <div>
            <p className="home-eyebrow">Tiempos</p>
            <h2 id="profile-times-title">Performance times</h2>
          </div>
          <button className="times-sort-button" type="button" onClick={onSortToggle}>
            {sortOrder === 'recent' ? 'Most recent' : 'Oldest first'}
          </button>
        </div>

        <div className="times-filter-row">
          <label>
            <span>Style</span>
            <select value={selectedStyle} onChange={onStyleChange}>
              <option value="All">All</option>
              {Object.keys(TIME_DISTANCES_BY_STYLE).map((style) => (
                <option key={style} value={style}>{style}</option>
              ))}
            </select>
          </label>
          <label>
            <span>Distance</span>
            <select value={selectedDistance} onChange={onDistanceChange}>
              <option value="All">All</option>
              {distanceOptions.map((distance) => (
                <option key={distance} value={distance}>{distance}</option>
              ))}
            </select>
          </label>
          <button className="times-filter-button" type="button" onClick={onFilterApply}>
            Filter
          </button>
        </div>

        {times.length === 0 && (
          <p className="times-empty">No times are linked to this swimmer yet.</p>
        )}

        {times.length > 0 && filteredTimes.length === 0 && (
          <p className="times-empty">No times match the selected filters.</p>
        )}

        {filteredTimes.length > 0 && (
          <div className="times-table-wrapper">
            <table className="times-table">
              <thead>
                <tr>
                  <th>Test</th>
                  <th>Time</th>
                  <th>Date</th>
                  <th>Location</th>
                </tr>
              </thead>
              <tbody>
                {filteredTimes.map((timeRecord) => (
                  <tr key={timeRecord.id}>
                    <td>{timeRecord.test_type}</td>
                    <td>{timeRecord.time}</td>
                    <td>{formatReadableDate(timeRecord.date)}</td>
                    <td>{timeRecord.location || 'N/A'}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </section>
    </section>
  )
}

function TrainingGroupPage({ categorySummary, error, isLoading, trainingGroup, weekDays, weekItems }) {
  const hasSchedule = Array.isArray(trainingGroup?.schedule) && trainingGroup.schedule.length > 0

  return (
    <section className="training-group-page" aria-labelledby="training-group-title">
      <header className="training-group-hero">
        <p className="home-eyebrow">Training group</p>
        <h1 id="training-group-title">{trainingGroup?.name || 'No training group'}</h1>
        <p>{trainingGroup?.categories || categorySummary || 'No category linked to this profile.'}</p>
      </header>

      <section className="training-week-section" aria-labelledby="training-week-title">
        <div className="training-week-heading">
          <div>
            <p className="home-eyebrow">Schedule</p>
            <h2 id="training-week-title">Current week</h2>
          </div>
          <span>{formatWeekRange(weekDays)}</span>
        </div>

        {isLoading && <p className="training-week-feedback">Loading training group...</p>}
        {!isLoading && error && <p className="training-week-feedback training-week-error">{error}</p>}
        {!isLoading && !error && !trainingGroup && (
          <p className="training-week-feedback">No training group is linked to this swimmer category yet.</p>
        )}
        {!isLoading && !error && trainingGroup && !hasSchedule && (
          <p className="training-week-feedback">This training group has no schedule blocks yet.</p>
        )}

        {!isLoading && !error && trainingGroup && (
          <div className="training-week-scroll">
            <div className="training-week-calendar" aria-label="Training group weekly schedule">
              <div className="training-week-corner" />
              {weekDays.map((day) => (
                <div className="training-day-header" key={day.dateKey}>
                  <span>{day.label}</span>
                  <strong>{day.date.getDate()}</strong>
                </div>
              ))}

              <div className="training-time-column">
                {TRAINING_WEEK_HOURS.map((hour) => (
                  <div className="training-time-label" key={hour}>
                    {formatHourValue(hour)}
                  </div>
                ))}
              </div>

              {weekDays.map((day) => (
                <div className="training-day-column" key={day.dateKey}>
                  {TRAINING_WEEK_HOURS.map((hour) => (
                    <div className="training-hour-slot" key={`${day.dateKey}-${hour}`} />
                  ))}
                  {(weekItems[day.dateKey] || []).map((item) => (
                    <div
                      className={`training-week-block ${item.className}`}
                      key={item.id}
                      style={getTrainingBlockStyle(item)}
                    >
                      <strong>{item.title}</strong>
                      <span>{item.subtitle}</span>
                    </div>
                  ))}
                </div>
              ))}
            </div>
          </div>
        )}
      </section>
    </section>
  )
}

function getLinkedSwimmers(appData, session) {
  if (appData?.swimmers?.length) {
    return appData.swimmers
  }

  if (session.swimmers?.length) {
    return session.swimmers
  }

  const singleSwimmer = appData?.swimmer || session.swimmer

  return singleSwimmer ? [singleSwimmer] : []
}

function formatUniqueList(values) {
  return [...new Set(values.filter(Boolean))].join(', ')
}

function getCalendarTypeLabel(dayType) {
  const labels = {
    holiday_training: 'Holiday training',
    holiday_no_training: 'Holiday off',
    registration_deadline: 'Registration deadline',
    schedule_change: 'Schedule change',
    training_suspended: 'Training suspended',
  }

  return labels[dayType] || dayType
}

function parseDateValue(value) {
  if (!value) {
    return null
  }

  const [datePart] = String(value).split('T')
  const [year, month, day] = datePart.split('-').map(Number)

  if (!year || !month || !day) {
    return null
  }

  return new Date(year, month - 1, day)
}

function getSection(path, isControlUser = false) {
  if (isControlUser) {
    return 'calendar'
  }

  if (path === '/app/profile') {
    return 'profile'
  }

  if (path === '/app/training-group') {
    return 'training'
  }

  return 'calendar'
}

function startOfMonth(date) {
  return new Date(date.getFullYear(), date.getMonth(), 1)
}

function startOfWeek(date) {
  const startDate = new Date(date.getFullYear(), date.getMonth(), date.getDate())
  const dayOffset = (startDate.getDay() + 6) % 7

  startDate.setDate(startDate.getDate() - dayOffset)
  startDate.setHours(0, 0, 0, 0)

  return startDate
}

export default AppIndex
