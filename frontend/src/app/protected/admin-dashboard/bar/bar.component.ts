import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { CanvasJS, CanvasJSAngularChartsModule } from '@canvasjs/angular-charts';
import { SalesBarService } from '../../../services/protected/sales-bar.service';

@Component({
  selector: 'app-bar',
  imports: [CommonModule, CanvasJSAngularChartsModule],
  templateUrl: './bar.component.html',
  styleUrl: './bar.component.css'
})
export class BarComponent {
  isLoading: boolean = true;
  chartOptions: any = {
    title: {
      text: "Total de Ventas por Categorías"
    },
    animationEnabled: true,
    axisY: {
      includeZero: true,
      suffix: "€"
    },
    data: [{
      type: "bar",
      indexLabel: "{y}",
      yValueFormatString: "#,###€",
      dataPoints: []
    }]
  };

  constructor(private salesBarService: SalesBarService) { }

  ngOnInit(): void {
    this.loadData();
  }

  loadData(): void {
    this.salesBarService.getVentas().subscribe({
      next: (data) => {
        // Actualización inmutable para detectar cambios
        this.chartOptions = {
          ...this.chartOptions,
          data: [{
            ...this.chartOptions.data[0],
            dataPoints: data
          }]
        };
        this.isLoading = false;
      },
      error: (error) => {
        console.error('Error:', error);
        this.isLoading = false;
      }
    });
  }

  // Getter para acceder a los dataPoints desde el template
  get dataPoints(): any[] {
    return this.chartOptions.data[0].dataPoints;
  }
    
  // Método para refrescar grafico
  refrescarGrafico(): void {
      console.log('Refrescando grafico...');
      this.loadData();
  }
}