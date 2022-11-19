import axios from 'axios';

const config = {
  headers: { 'Content-Type': 'application/json', 'Access-Control-Allow-Origin': '*' },
  timeout: 30000,
};

export class Api {
  static async post(url, data, configure) {
    return new Promise((resolve) => {
      axios
        .post(url, data, { ...config, ...configure })
        .then(
          response => resolve(response),
        )
        .catch(err => {
          if (err.response) {
            // client received an error response (5xx, 4xx)
            resolve(err.response);
          } else if (err.request) {
            // client never received a response, or request never left
            console.log(err.request);
          } else {
            // anything else
            console.log(err);
          }
        });
    });
  }

  static async get(url, params) {
    return new Promise((resolve, reject) => {
      axios
        .get(url, { ...config, params })
        .then(
          response => resolve(response.data),
          err => {
            reject(err);
          },
        )
        .catch(error => reject(error));
    });
  }
}
