import { Options } from '@angular-slider/ngx-slider';
import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormControl } from '@angular/forms';
import { ServersService } from 'src/app/services/servers.service';

@Component({
  selector: 'app-servers',
  templateUrl: './servers.component.html',
  styleUrls: ['./servers.component.scss'],
})
export class ServersComponent implements OnInit {
  locations: any = [];
  location: any = null;
  servers: any = null;
  checkedRams: any = [];
  isLoading: boolean = false;
  filterForm = this.formBuilder.group({
    disk_type: new FormControl('', []),
    ram: new FormControl('', []),
    location: new FormControl('', []),
  });
  minValue: number = 0.25;
  maxValue: number = 1;
  options: Options = {
    floor: 0,
    ceil: 72,
    showTicksValues: true,
    stepsArray: [
      {
        value: 0,
        legend: '0',
      },
      {
        value: 0.25,
        legend: '250GB',
      },
      {
        value: 0.5,
        legend: '500GB',
      },
      {
        value: 1,
        legend: '1TB',
      },
      {
        value: 2,
        legend: '2TB',
      },
      {
        value: 3,
        legend: '3TB',
      },
      {
        value: 4,
        legend: '4TB',
      },
      {
        value: 8,
        legend: '8TB',
      },
      {
        value: 12,
        legend: '12TB',
      },
      {
        value: 24,
        legend: '24TB',
      },
      {
        value: 48,
        legend: '48TB',
      },
      {
        value: 72,
        legend: '72TB',
      },
    ],
  };

  constructor(
    private serversService: ServersService,
    private formBuilder: FormBuilder
  ) {}

  async ngOnInit() {
    this.getData();
  }

  async getData() {
    this.serversService.getLocations().subscribe((res) => {
      this.locations = res;
    });
    let filters = {
      location: null,
      ram: null,
      disk_type: null,
      storage_min: this.minValue,
      storage_max: this.maxValue,
    };
    this.filterServers(filters);
  }
  onSubmit() {
    let filters = {
      location: this.location ? this.location : null,
      ram:
        this.checkedRams.length > 0 ? JSON.stringify(this.checkedRams) : null,
      disk_type: this.filterForm.controls['disk_type'].value
        ? this.filterForm.controls['disk_type'].value
        : null,
      storage_min: this.minValue,
      storage_max: this.maxValue,
    };
    this.filterServers(filters);
  }

  filterServers(filters: {}) {
    this.isLoading = true;
    this.servers = [];
    this.serversService.searchServers(filters).subscribe((res) => {
      this.servers = res;
      this.isLoading = false;
    });
  }

  changeRamValues(data: any) {
    if (data.target.checked) {
      this.checkedRams.push(data.target.value);
    } else {
      this.checkedRams = this.checkedRams.filter(
        (v: any) => v != data.target.value
      );
    }
  }
}
