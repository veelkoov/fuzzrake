# Authentication

## Limitations, known issues

- Usernames are not supported; only email addresses. `grep-code-username-is-email`
- During registration, user sees "email already registered" when trying to reuse email address. `grep-code-email-already-registered`
- User can log in before confirming email address (but should not be able to do anything except re-requesting the verification email). `grep-code-user-can-login-before-email-verification`

## References

1. https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html
