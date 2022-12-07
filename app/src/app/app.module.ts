import { APP_INITIALIZER, NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { ServersComponent } from './components/servers/servers.component';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { SidebarComponent } from './components/sidebar/sidebar.component';
import { HttpClientModule } from '@angular/common/http';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { NgxSliderModule } from '@angular-slider/ngx-slider';
import { RuntimeEnvLoaderService } from './services/runtime-env-loader.service';

const appInitializerFn = (envLoader: RuntimeEnvLoaderService) => {
  return () => {
    return envLoader.loadAppConfig();
  };
};

@NgModule({
  declarations: [AppComponent, ServersComponent, SidebarComponent],
  imports: [
    BrowserModule,
    AppRoutingModule,
    NgbModule,
    HttpClientModule,
    FormsModule,
    ReactiveFormsModule,
    NgxSliderModule,
  ],
  providers: [
    RuntimeEnvLoaderService,
    {
      provide: APP_INITIALIZER,
      useFactory: appInitializerFn,
      multi: true,
      deps: [RuntimeEnvLoaderService],
    },
  ],
  bootstrap: [AppComponent],
})
export class AppModule {}
