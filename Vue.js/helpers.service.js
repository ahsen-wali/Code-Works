import Vue from 'vue'
import AxiosService from './axios.service'

export const helperService = (function () {
  function multiSelectDisplay (values) {
    return values.map(va => va.text).join(', ') + ` (${values.length})`
  }

  const showToast = async (variant = 'success', message) => {
    const vm = new Vue()
    vm.$toast.open({ message, type: variant, position: 'top-right' })
  }

  const loadingSpin = async (status = true) => {
    const vm = new Vue()
    return vm.$loading(status)
  }

  const showConfirm = async (title, message = 'Please confirm that you want to create new account.') => {
    const vm = new Vue()
    return vm.$bvModal.msgBoxConfirm(message,
      {
        title,
        size: 'sm',
        buttonSize: 'sm',
        okVariant: 'primary',
        okTitle: 'Confirm',
        cancelTitle: 'Cancel',
        footerClass: 'p-2',
        hideHeaderClose: false,
        centered: true
      }
    )
  }

  const uploadFile = async (file) => {
    return new Promise((resolve, reject) => {
      AxiosService.postWithFile('common/upload-file', { image: file }).then(({ data }) => {
        resolve(data)
      }).catch(() => {})
    })
  }

  return {
    multiSelectDisplay,
    showToast,
    loadingSpin,
    showConfirm,
    uploadFile
  }
})()

export default helperService
