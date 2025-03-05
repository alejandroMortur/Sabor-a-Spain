import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { CanvasJSAngularChartsModule } from '@canvasjs/angular-charts';
import { GrafService } from '../../../services/protected/graf.service';

@Component({
  selector: 'app-graf',
  imports: [CommonModule, CanvasJSAngularChartsModule],
  templateUrl: './graf.component.html',
  styleUrl: './graf.component.css'
})
export class GrafComponent {
   // Inicializa chartOptions con estructura básica
   chartOptions: any = { 
    data: [] 
  };
  
  dataPoints: any[] = [];
  isLoading: boolean = true; // Bandera de carga

  constructor(private grafService: GrafService) { }

  ngOnInit(): void {
    this.loadGraficoData();
  }

  loadGraficoData(): void {
    this.grafService.getGraficoStock().subscribe({
      next: (data) => {
        this.dataPoints = data.map(item => ({
          name: item.name,
          y: item.y
        }));

        this.chartOptions = {
          animationEnabled: true,
          theme: "dark2",
          exportEnabled: true,
          title: {
            text: "Grafica total stock"
          },
          subtitles: [{
            text: "Grafico existencias stock de productos"
          }],
          data: [{
            type: "pie",
            indexLabel: "{name}: {y}",
            dataPoints: this.dataPoints
          }]
        };
        
        this.isLoading = false; // Datos cargados
      },
      error: (error) => {
        console.error('Error:', error);
        this.isLoading = false; // Ocultar loader en caso de error
      }
    });
  }
  // Método para refrescar grafico
  refrescarGrafico(): void {
      console.log('Refrescando grafico...');
      this.loadGraficoData();
  }
}
