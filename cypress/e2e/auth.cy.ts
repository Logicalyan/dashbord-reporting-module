describe('Authentication', () => {
  it('shows login page', () => {
    cy.visit('/login')
    cy.contains('Login')
  })

  it('rejects invalid login', () => {
    cy.visit('/login')

    cy.get('input[name=email]').type('wrong@email.com')
    cy.get('input[name=password]').type('wrongpass')
    cy.get('button[type=submit]').click()

    cy.contains('Invalid credentials')
  })

  it('logs in user successfully', () => {
    cy.login('test@example.com', 'password123')

    cy.visit('/dashboard')
    cy.contains('Dashboard')
  })
})
