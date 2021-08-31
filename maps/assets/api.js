import axios from 'axios'

export function getSuggestions(filter) {
    const url = `http://127.0.0.1:8000/api/search/${filter}`
    return axios.get(url)
        .then((response) => {
            return response.data;
        })
        .catch((err) => {
            console.log(err)
        })
}