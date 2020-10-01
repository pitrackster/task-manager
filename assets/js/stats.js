
$(() => {
    const startDateElement = document.getElementById('date-start')
    const endDateElement = document.getElementById('date-end')
    let startDateHiddenElement = document.getElementById('date-start-hidden')
    let endDateHiddenElement = document.getElementById('date-end-hidden')

    startDateElement.addEventListener('change', (e) => {
        console.log('start date changed', e.target.value)
        startDateHiddenElement.value = e.target.value
    })

    endDateElement.addEventListener('change', (e) => {
        console.log('start date changed', e.target.value)
        endDateHiddenElement.value = e.target.value
    })

});
