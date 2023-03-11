/**
 * @author mimimiku778 <0203.sub@gmail.com>
 * @license https://github.com/mimimiku778/MimimalCMS/blob/master/LICENSE.md
 */

/**
 * Shorthand function for document.querySelector, which returns the first matching element within the document.
 *
 * @function qS
 * @param {string} selector - The selector to use for finding the element.
 * @returns {Element|null} - The first matching element, or null if none is found.
 */
const qS = document.querySelector.bind(document)

/**
 * Shorthand function for document.querySelectorAll, which returns a NodeList of all matching elements within the document.
 *
 * @function qSA
 * @param {string} selector - The selector to use for finding the elements.
 * @returns {NodeList} - A NodeList of all matching elements.
 */
const qSA = document.querySelectorAll.bind(document)

/**
 * Finds an element by its ID.
 *
 * @param {string} id - The ID of the element to find.
 * @returns {Element|null} - The matching element, or null if none is found.
 */
const byId = id => document.getElementById(id)

/**
 * Adds a click event listener to an element, if it exists.
 *
 * @param {Element|null} element - The element to add the listener to.
 * @param {Function} callback - The function to execute when the element is clicked.
 */
const addClick = (element, callback) => element && element.addEventListener('click', callback)

/**
 * Enables/disables a button based on the input field value.
 * 
 * @param {HTMLInputElement} input - The input field element.
 * @param {HTMLButtonElement} button - The button element to enable/disable.
 */
const toggleButtonByInputValue = (input, button) => {
  input.addEventListener('input', () => {
    const normalizedStr = input.value.normalize('NFKC');
    const string = normalizedStr.replace(/[\u200B-\u200D\uFEFF]/g, '');
    button.disabled = string.trim() === '';
  });
};

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
