import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Router, UrlSerializer } from '@angular/router';
import { catchError, filter, map } from 'rxjs';
import { RuntimeEnvLoaderService } from './runtime-env-loader.service';

@Injectable({
  providedIn: 'root',
})
export class ServersService {
  basePath: string;

  constructor(
    private http: HttpClient,
    private serializer: UrlSerializer,
    private router: Router,
    private envLoader: RuntimeEnvLoaderService
  ) {
    this.basePath = envLoader.config.API_BASE_URL;
  }

  getLocations() {
    const url = this.basePath + '/get-locations';
    return this.http.get<any>(url).pipe(
      map((response) => {
        return response;
      })
    );
  }

  searchServers(filters: {}) {
    const url = this.basePath + '/filter-servers';
    const urlParams = this.router.createUrlTree([''], {
      queryParams: filters,
    });

    return this.http
      .get<any>(url + this.serializer.serialize(urlParams), {
        observe: 'response',
      })
      .pipe(
        map((response) => {
          return response.body;
        })
      );
  }
}
