import axios from 'axios';

const config = {
  headers: { 'Content-Type': 'application/json', 'Access-Control-Allow-Origin': '*' },
  timeout: 30000,
};

export class Api {
  static async post(url, data, configure) {
    return new Promise(resolve => {
      axios
        .post(url, data, { ...config, ...configure })
        .then(response => resolve(response))
        .catch(err => {
          if (err.response) {
            // client received an error response (5xx, 4xx)
            resolve(err.response);
          }
        });
    });
  }

  static async put(url, data, configure) {
    return new Promise(resolve => {
      axios
        .put(url, data, { ...config, ...configure })
        .then(response => resolve(response))
        .catch(err => {
          if (err.response) {
            // client received an error response (5xx, 4xx)
            resolve(err.response);
          }
        });
    });
  }

  static async get(url, params) {
    return new Promise(resolve => {
      axios
        .get(url, { ...config, params })
        .then(response => resolve(response.data))
        .catch(err => {
          if (err.response) {
            // client received an error response (5xx, 4xx)
            resolve(err.response);
          }
        });
    });
  }
}
