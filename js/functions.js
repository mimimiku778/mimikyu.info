/**
 * @author mimimiku778 <0203.sub@gmail.com>
 * @license https://github.com/mimimiku778/MimimalCMS/blob/master/LICENSE.md
 */

/**
 * A shorthand function for `document.querySelector`, which returns the first matching element within a given element or the document.
 *
 * @function qS
 * @param {string} selector - The selector to use for finding the element.
 * @param {Element=} element - The element within which to search for the selector. Defaults to `document`.
 * @returns {?Element} - The first matching element, or null if none is found.
 */
const qS = (s, element = document) => element.querySelector(s)

/**
 * A shorthand function for `document.querySelectorAll`, which returns a NodeList of all matching elements within a given element or the document.
 *
 * @function qSA
 * @param {string} selector - The selector to use for finding the elements.
 * @param {Element=} element - The element within which to search for the selector. Defaults to `document`.
 * @returns {!NodeList} - A NodeList of all matching elements.
 */
const qSA = (s, element = document) => element.querySelectorAll(s)

/**
 * Finds an element by its ID.
 *
 * @param {string} id - The ID of the element to find.
 * @returns {Element|null} - The matching element, or null if none is found.
 */
const byId = id => document.getElementById(id)

/**
 * Validates if a string is not empty or only contains white space.
 * 
 * @param {string} str - The string to validate.
 * @returns {boolean} - Returns true if the string is not empty and does not only contain white space, false otherwise.
 */
const validateStringNotEmpty = str => {
  const normalizedStr = str.normalize('NFKC')
  const string = normalizedStr.replace(/[\u200B-\u200D\uFEFF]/g, '')
  return string.trim() !== ''
}

/**
 * Remove zero-width spaces from a string.
 *
 * @param string $str The input string.
 * @return string     The input string without zero-width spaces.
 */
const removeZWS = str => {
  const normalizedStr = str.normalize('NFKC')
  const string = normalizedStr.replace(/[\u200B-\u200D\uFEFF]/g, '')
  return string
}

/**
 * Sends a POST request to the specified URL with form data.
 *
 * @param {string} url - The URL to send the request to.
 * @param {Object|HTMLFormElement} [obj={}]
 *  The form data to include in the request, can be an object or an HTML form element.
 *  If an HTML form element is passed, the JSON will be generated from the form.
 * @param {function} [callback=null]
 *  An optional callback function to execute when the response is received.
 *  The function will be called with an object that has two properties:
 *  - data: The response data returned from the server.
 *  - code: The HTTP status code returned from the server.
 * 
 * @example
 * // Sending a POST request with an object as form data and logging the response
 * const obj = {
 *   name: 'John',
 *   email: 'john@example.com'
 * }
 * sendPostRequest('https://example.com/api', obj, ({ data, code }) => {
 *   console.log(data, `status code: ${code}`)
 * })
 * 
 * @example
 * // Sending a POST request with a form element as form data and displaying an alert
 * const form = document.querySelector('#myForm')
 * sendPostRequest('https://example.com/api', form, ({ data, code }) => {
 *   alert(`Response data: ${JSON.stringify(data)}, status code: ${code}`)
 * })
 */
const sendPostRequest = async (url, obj = {}, callback = null) => {
  let body = null

  if (obj instanceof HTMLFormElement) {
    const formData = new FormData(obj)
    body = JSON.stringify(Object.fromEntries(formData.entries()))
  } else if (obj instanceof Object) {
    body = JSON.stringify(obj)
  } else {
    console.error('Error: sendPostRequest: Invalid form data')
    return
  }

  try {
    const response = await fetch(url, {
      method: 'POST', body, headers: { 'Content-Type': 'application/json' }
    })
    const data = await response.json()
    if (callback) callback({ data, code: response.status })
  } catch (error) {
    console.error(error)
  }
}
