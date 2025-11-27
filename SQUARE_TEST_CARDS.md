# Square Sandbox Test Cards

When testing payments in Square Sandbox mode, you must use specific test card numbers. Using real card numbers or incorrect test cards will result in errors like `GENERIC_DECLINE`.

## Valid Square Sandbox Test Cards

### Successful Payment Test Cards

**Visa (Success):**
- Card Number: `4111 1111 1111 1111`
- CVV: Any 3 digits (e.g., `111`)
- Expiration: Any future date (e.g., `12/25`)

**Mastercard (Success):**
- Card Number: `5555 5555 5555 4444`
- CVV: Any 3 digits (e.g., `111`)
- Expiration: Any future date (e.g., `12/25`)

**American Express (Success):**
- Card Number: `3782 822463 10005`
- CVV: Any 4 digits (e.g., `1111`)
- Expiration: Any future date (e.g., `12/25`)

### Test Cards for Specific Scenarios

**Insufficient Funds:**
- Card Number: `4000 0000 0000 9995`
- CVV: Any 3 digits
- Expiration: Any future date

**CVV Failure:**
- Card Number: `4000 0000 0000 0127`
- CVV: Any 3 digits (will fail CVV check)
- Expiration: Any future date

**Expired Card:**
- Card Number: `4000 0000 0000 0069`
- CVV: Any 3 digits
- Expiration: Any past date

**Generic Decline:**
- Card Number: `4000 0000 0000 0002`
- CVV: Any 3 digits
- Expiration: Any future date

## Important Notes

1. **Always use test cards in Sandbox mode** - Real card numbers will not work and may cause errors
2. **Use any valid CVV** - Square sandbox accepts any CVV for test cards
3. **Use any future expiration date** - Except for expired card testing
4. **Use any valid postal code** - Square sandbox accepts any postal code for test cards
5. **Use any valid name** - Square sandbox accepts any name for test cards

## Common Errors and Solutions

### GENERIC_DECLINE Error

If you're getting `GENERIC_DECLINE` errors:

1. **Verify you're using a valid Square test card** (see above)
2. **Check that Sandbox mode is enabled** in Admin Panel > Settings > Payment Settings
3. **Verify your Square credentials** are correct:
   - Application ID (should start with `sandbox-` in sandbox mode)
   - Location ID
   - Access Token (sandbox access token)
4. **Check the error logs** (`php-error.log`) for detailed error information

### Testing Payment Flow

1. Add items to cart
2. Proceed to checkout
3. Fill in billing/shipping information
4. On payment page, use one of the test cards above
5. Payment should process successfully

## Production Mode

When switching to production mode:

1. Update Square credentials in Admin Panel > Settings > Payment Settings
2. Change Mode from "Sandbox / Testing" to "Live / Production"
3. Use real card numbers (customers will use their actual cards)
4. Test with a small amount first to verify everything works

---

**Note:** These test cards only work in Square Sandbox mode. They will NOT work in production mode.

