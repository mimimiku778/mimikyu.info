/**
 * Utility class for working with JSON-encoded cookies in the browser.
 * 
 * @author mimimiku778 <0203.sub@gmail.com>
 * @license https://github.com/mimimiku778/MimimalCMS/blob/master/LICENSE.md
 */
class JsonCookie {
  // A boolean flag indicating whether the `Secure` attribute of a cookie should be set or not.
  secure = false

  /**
   * Creates a new `JsonCookie` instance with the given name and expiration time.
   * 
   * @param {string} name - The name of the cookie.
   * @param {number|null} [expiresSeconds=null] - The number of seconds until the cookie expires, or `null` for a session cookie.
   */
  constructor(name = 'cookie', expiresSeconds = null) {
    this.name = name;
    this.expiresSeconds = expiresSeconds;
  }

  /**
   * Gets the value of the cookie, optionally filtered by a specific key.
   * 
   * @param {string|null} [key=null] - The key to filter the cookie by, or `null` to get the entire cookie object.
   * @returns {*} The value of the cookie, or `undefined` if the cookie is not found or is not a valid JSON string.
   * 
   * @example
   * const cookie = new JsonCookie('myCookie')
   * const entireCookie = cookie.get()
   * const specificValue = cookie.get('myKey')
   */
  get(key = null) {
    const cookieRegex = new RegExp(`(^|;)\\s*${this.name}\\s*=\\s*([^;]+)`)
    const cookieMatch = document.cookie.match(cookieRegex)

    if (!cookieMatch) {
      console.error(`Error: Cookie ${this.name} not found.`)
      return undefined
    }

    let parsedCookieValue = {}
    try {
      const cookieValue = decodeURIComponent(cookieMatch[2])
      parsedCookieValue = JSON.parse(cookieValue)
    } catch (e) {
      console.error(`Error: ${this.name} cookie is not a valid JSON string.`, e)
      return undefined
    }

    if (!parsedCookieValue) {
      console.error(`Error: ${this.name} cookie is not a valid JSON string.`)
      return undefined
    }

    if (!key) {
      return parsedCookieValue
    }

    if (!parsedCookieValue.hasOwnProperty(key)) {
      console.error(`Error: Key ${key} not found in cookie ${this.name}.`)
      return undefined
    }

    return parsedCookieValue[key]
  }

  /**
   * Sets the value of the cookie, either as a key-value pair or an entire object.
   * 
   * @param {string|object} keyOrData - The key to set, or an object to set as the entire cookie value.
   * @param {*} [value=null] - The value to set for the given key, if `keyOrData` is a string.
   * 
   * @example
   * const cookie = new JsonCookie('myCookie')
   * cookie.set('myKey', 'myValue')
   * cookie.set({ myKey: 'myValue', myOtherKey: 'myOtherValue' })
   */
  set(keyOrData, value = null) {
    if (!this.name) {
      console.error('Error: Cookie name is required.')
      return
    }

    let cookieData = this.get() || {}

    if (typeof keyOrData === 'object') {
      cookieData = keyOrData
    } else if (keyOrData) {
      cookieData[keyOrData] = value
    } else {
      console.error('Error: Cookie key or data is required.')
      return
    }

    const encodedData = encodeURIComponent(JSON.stringify(cookieData))

    let expires = ''
    if (this.expiresSeconds) {
      const expirationDate = new Date(Date.now() + this.expiresSeconds * 1000)
      expires = `;expires=${expirationDate.toUTCString()}`
    }

    const cookieString = `${this.name}=${encodedData}${expires};${this.secure ? 'Secure;' : ''}`
    document.cookie = cookieString
  }

  /**
   * Removes the cookie or a specific key-value pair from the cookie.
   * 
   * @param {string|null} [key=null] - The key to remove, or `null` to remove the entire cookie.
   * 
   * @example
   * const cookie = new JsonCookie('myCookie')
   * cookie.remove()
   * cookie.remove('myKey')
   */
  remove(key = null) {
    let cookieString = `${this.name}=;expires=Thu, 01 Jan 1970 00:00:01 GMT;${this.secure ? 'Secure;' : ''}`

    if (key) {
      const cookieData = this.get() || {}
      delete cookieData[key]
      const encodedData = encodeURIComponent(JSON.stringify(cookieData))

      let expires = ''
      if (this.expiresSeconds) {
        const expirationDate = new Date(Date.now() + this.expiresSeconds * 1000)
        expires = `;expires=${expirationDate.toUTCString()}`
      }

      cookieString = `${this.name}=${encodedData}${expires};${this.secure ? 'Secure;' : ''}`
    }

    document.cookie = cookieString
  }
}
